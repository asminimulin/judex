import os
import pathlib


class Config:

    # If DEBUG is True then send debug output with 500 status on error
    DEBUG = False


class ProductionConfig(Config):
    DEBUG = False


class DevelopmentConfig(Config):
    DEBUG = True  # override Config.DEBUG

    # Show interactive debugger in browser
    DEVELOPMENT = True

