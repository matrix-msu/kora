from json import load
import os


def env(string):
    """
    A function to emulate the functionality of the env helper in the Laravel framework.

    :raises IndexError: on invalid name.
    :param string: a valid database env variable name.
    :return string: the value of the environment variable.
    """

    ## The /python directory in the Kora 3 files.
    python_dir = os.path.dirname(os.path.abspath(__file__))

    with open(os.path.join(python_dir, "env.json"), "r") as env_file:
        env_json = load(env_file)

        return env_json[string]
