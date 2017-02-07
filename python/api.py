#!/usr/bin/env python2.7

import os
import sys
import shutil
from export import export_routine

if __name__ == "__main__":
    filepath = export_routine(sys.argv)
    with open(filepath, "r") as f:
        shutil.copyfileobj(f, sys.stdout)

        os.remove(filepath)
