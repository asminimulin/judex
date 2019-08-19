#!/usr/bin/python3
# This file contains some most important constants.

# Builtin imports
import os

JUDEX_HOME = os.getenv('JUDEX_HOME')

def is_running(pid : int):
    try:
        os.kill(pid, 0)
    except:
        return False
    return True
