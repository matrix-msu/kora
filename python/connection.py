import MySQLdb
from MySQLdb import cursors
from env import env
from table import Table, BaseFieldTypes, get_data_names

##
## Connection: functions as a MySQL Database connection to guard from making mistakes.
## Cursor: function as a MySQL Cursor, with custom methods to limit global cursors.
##

class Connection:
    """
    Adapter for the MySQLdb interface.
    """
    def __init__(self):
        """
        Connection constructor.
        Uses the environment variables defined in env.json.
        """
        connection_dict = {
            "host": env("DB_HOST"),
            "user": env("DB_USERNAME"),
            "passwd": env("DB_PASSWORD"),
            "db": env("DB_DATABASE"),
            ##
            ## We want the results returned as a dictionary, but not have potentially thousands dumped into memory.
            ## So we use the SSDictCursor which stores the results on the server (MySQL Server) and fetches as needed.
            ##
            "cursorclass": cursors.SSDictCursor
        }

        self._cnx = MySQLdb.connect(**connection_dict) ## Spawn a cursor from here.

    def __del__(self):
        """
        Close the connection to the database
        """
        self._cnx.close()

    def cursor(self):
        """
        Get a new cursor from the connection.
        :return MySQLdb.cursors.DictCursor:
        """
        return self._cnx.cursor()

class Cursor:
    """
    Adapter for the MysQLdb cursor.
    Cursors execute queries.

    *** Note that cursors should not ever be returned to the user outside this class.
        Global cursors and other unforeseen methods called on cursors are unpredictable.
    """
    def __init__(self, connection):
        """
        Cursor constructor.
        :param connection: MySQLdb connection
        """
        self._cnx = connection
        self._prefix = env("DB_PREFIX")

    def get_typed_fields(self, rids, table_name):
        """
        A generator that yields typed fields of a group of records (represented by their rids).

        :param rids: list of rids to query with
        :param table_name: table name (must be in table.py)
        :raise Exception: when an invalid table name is provided.
        :return dict: dictionary representing a typed field.
        """

        ## Initialize cursor from DB Connection.
        cursor = self._cnx.cursor()

        ## Double check to make sure the table_name is valid.
        if not table_name in BaseFieldTypes.__dict__.values():
            raise Exception("Invalid table name in get_typed_fields.")

        stmt = "SELECT * FROM " + self._prefix + table_name + " "

        stmt += "WHERE `rid` = %s"
        for _ in range(len(rids) - 1):
            stmt += " OR `rid` = %s"

        cursor.execute(stmt, rids)

        for row in cursor:
            yield row

        cursor.close()

    def get_field_stash(self, fid):
        """
        Creates a field information stash.

        :param fid: form id
        :return dict: dictionary containing the field stash with indexing:
                stash[flid]['slug'] = field slug
                stash[flid]['type'] = field type (Text, Rich Text, etc.)
        """

        ## Initialize cursor.
        cursor = self._cnx.cursor()

        stmt = "SELECT `flid`, `slug`, `type`, `options` FROM " + self._prefix + "fields WHERE `fid` = %s"

        cursor.execute(stmt, [fid])

        stash = dict()
        for row in cursor:
            stash[row["flid"]] = {"slug": row["slug"], "type": row["type"], "options": row["options"]}

        cursor.close()

        return stash

    def get_form_resource_title(self, fid):
        """
        Gets the forms resource title
        :param fid: form id
        :return string: metadata resource title
        """
        cursor = self._cnx.cursor()

        stmt = "SELECT `lod_resource` FROM " + self._prefix + "forms WHERE `fid` = %s"

        cursor.execute(stmt, [fid])

        row = cursor.fetchone()
        resource = row["lod_resource"]

        cursor.close()

        return resource

    def get_resource_index_value(self, fid, rid):
        """
        Gets a records value for primary index field
        :param fid: form id
        :param rid: record id
        :return string: resource value
        """
        cursor = self._cnx.cursor()

        ## gets the primary field
        stmt = "SELECT `flid` FROM " + self._prefix + "metadatas WHERE `fid` = %s and `primary` = 1"

        cursor.execute(stmt, [fid])

        row = cursor.fetchone()
        flid = row["flid"]

        cursor.close()

        cursor2 = self._cnx.cursor()

        ##gets the textfields value, since primary field must be text
        stmt2 = "SELECT `text` FROM " + self._prefix + "text_fields WHERE `flid` = %s AND `rid` = %s"

        cursor2.execute(stmt2, [flid,rid])

        row = cursor2.fetchone()
        text = row["text"]

        cursor2.close()

        return text

    def pid_from_fid(self, fid):
        """
        Gets the project id associated with any particular form id.
        :param fid: form id.
        :return int: project id.
        """
        cursor = self._cnx.cursor()

        stmt = "SELECT `pid` FROM " + self._prefix + "forms WHERE `fid` = %s"

        cursor.execute(stmt, [fid])

        row = cursor.fetchone()
        pid = row["pid"]

        cursor.close()

        return pid

    def fid_from_rid(self, rid):
        """
        Gets the form id associated with any particular record id.
        :param rid: record id.
        :return int: form id.
        """
        cursor = self._cnx.cursor()

        stmt = "SELECT `fid` FROM " + self._prefix + "records WHERE `rid` = %s"

        cursor.execute(stmt, [rid])

        row = cursor.fetchone()
        fid = row["fid"]

        cursor.close()

        return fid

    def kid_from_rid(self, rid):
        """
        Gets the kid associated with any particular record id.
        :param rid: record id.
        :return string: kid
        """
        cursor = self._cnx.cursor()

        stmt = "SELECT `kid` FROM " + self._prefix + "records WHERE `rid` = %s"

        cursor.execute(stmt, [rid])

        row = cursor.fetchone()
        kid = row["kid"]

        cursor.close()

        return kid

    def meta_from_rid(self, rid):
        """
        Gets the owner associated with any particular record id.
        :param rid: record id.
        :return dict: meta
        """
        cursor = self._cnx.cursor()

        stmt = "SELECT `owner`,`created_at`,`updated_at` FROM " + self._prefix + "records WHERE `rid` = %s"

        cursor.execute(stmt, [rid])

        row = cursor.fetchone()
        owner = row["owner"]
        meta = {}
        meta["created"] = row["created_at"].strftime("%Y-%m-%d %H:%M:%S")
        meta["updated"] = row["updated_at"].strftime("%Y-%m-%d %H:%M:%S")

        cursor.close()

        cursor2 = self._cnx.cursor()

        stmt = "SELECT `username` FROM " + self._prefix + "users WHERE `id` = %s"

        cursor2.execute(stmt, [owner])

        row2 = cursor2.fetchone()
        meta["owner"] = row2["username"]

        cursor2.close()

        return meta

    def get_field_data(self, table, rid):
        """
        Gets the data from a particular table based on an rid, yielded by a generator.

        :param table: BaseField table name.
        :param rid: record id.
        :return dict: row from the database.
        """

        cursor = self._cnx.cursor()

        stmt = "SELECT `fid`, `flid`, `rid`" + get_data_names(table) + " FROM " + self._prefix + table + " WHERE `rid` = %s"

        cursor.execute(stmt, [rid])
        results = cursor.fetchall()

        if len(results) < 1: ## No fields were found.
            raise StopIteration

        for row in results:
            yield row

        cursor.close()

    def get_field_data_lod(self, table, rid):
        """
        Gets the data from a particular table based on an rid, yielded by a generator.

        :param table: BaseField table name.
        :param rid: record id.
        :return dict: row from the database.
        """

        cursor = self._cnx.cursor()

        stmt = "SELECT data.`flid`, data.`rid`, meta.`name`" + get_data_names(table) + " FROM " + self._prefix + table + " data LEFT JOIN " + self._prefix + "metadatas meta ON data.`flid` = meta.`flid` WHERE data.`rid` = %s and meta.`primary`=0"

        cursor.execute(stmt, [rid])
        results = cursor.fetchall()

        if len(results) < 1: ## No fields were found.
            raise StopIteration

        for row in results:
            yield row

        cursor.close()

    def get_project_slug(self, pid):
        """
        Gets project slug from its ID
        :param pid: project id.
        :return string: slug of project
        """
        cursor = self._cnx.cursor()

        stmt = "SELECT `slug` FROM " + self._prefix + "projects WHERE `pid` = %s"

        cursor.execute(stmt, [pid])

        row = cursor.fetchone()
        slug = row["slug"]

        cursor.close()

        return slug

    def get_form_slug(self, fid):
        """
        Gets form slug from its ID
        :param fid: form id.
        :return string: slug of form
        """
        cursor = self._cnx.cursor()

        stmt = "SELECT `slug` FROM " + self._prefix + "forms WHERE `fid` = %s"

        cursor.execute(stmt, [fid])

        row = cursor.fetchone()
        slug = row["slug"]

        cursor.close()

        return slug

    @staticmethod
    def get_support_fields(support_type, rid, flid):
        """
        Gets the support fields for a particular record.

        :param string support_type: the support field type.
        :param int|string rid: record id.
        :param int|string flid: field id.
        :return dict:
        """
        cnx = Connection()
        cursor = cnx.cursor()

        if support_type == Table.ScheduleSupport:
            stmt = "SELECT `begin`, `end`, `desc`, `allday` FROM " + env("DB_PREFIX") + Table.ScheduleSupport \
                   + " WHERE `rid` = %s AND `flid` = %s"

        elif support_type == Table.GeolocatorSupport:
            stmt = "SELECT `desc`, `lat`, `lon`, `zone`, `easting`, `northing`, `address` " \
            + "FROM " + env("DB_PREFIX") + Table.GeolocatorSupport + " WHERE `rid` = %s AND `flid` = %s"

        elif support_type == Table.AssociatorSupport:
            stmt = "SELECT `record` " \
            + "FROM " + env("DB_PREFIX") + Table.AssociatorSupport + " WHERE `rid` = %s AND `flid` = %s"

        else: # Combo Support
            stmt = "SELECT `list_index`, `field_num`, `data`, `number` " \
            + "FROM " + env("DB_PREFIX") + Table.ComboSupport + " WHERE `rid` = %s AND `flid` = %s " \
            + "ORDER BY `id` ASC"

        cursor.execute(stmt, [rid, flid])

        if not cursor:
            raise StopIteration

        for row in cursor:
            yield row

        cursor.close()
        del cnx