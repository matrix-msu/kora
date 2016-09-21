from json import load
import os


def env(string):
    """
    A function to emulate the functionality of the env helper in the Laravel framework.

    :raises IndexError: on invalid name.
    :param string: a valid database env variable name.
    :raise IndexError: on invalid input name.
    :return string: the value of the environment variable.
    """

    ## The /python directory in the Kora 3 files.
    python_dir = os.path.dirname(os.path.abspath(__file__))

    with open(os.path.join(python_dir, "..", ".env"), "r") as env_file:
        for line in env_file:
            line.strip()

            if len(line) <= 2:
                continue ## Ignore empty lines

            name, value = line.split("=")

            if name.strip() == string:
                return value.strip()

    raise IndexError("Index \"" + string + "\" not found in .env file")
