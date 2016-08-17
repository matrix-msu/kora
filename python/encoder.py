from json import JSONEncoder
from simplejson import JSONEncoderForHTML
from datetime import datetime

##
## DBEncoder: extends the JSONEncoder to deal with datetime objects.
##            JSON has no standard method of formatting dates (http://www.json.org/).
##

class DBEncoder(JSONEncoder):
    """
    Extends the JSONEncoder class to deal with date time errors.
    """

    def default(self, obj, to_file = True):
        """
        Extends the functionality of the JSONEncoder to deal with datetime objects.
        Datetime objects will be output in YYYY-MM-DD HH:MM:SS format (where HH is 24-hr).

        :param obj: the element to be encoded.
        :param to_file: bool, if the object is being encoded to a file.
        :return string: the encoded json string.
        """
        if isinstance(obj, datetime):
            return obj.strftime("%Y-%m-%d %H:%m:%S")

        return JSONEncoder.default(self, obj)