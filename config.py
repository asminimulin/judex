import os
import pathlib


class Config:

    # If DEBUG is True then send debug output with 500 status on error
    DEBUG = False
    SQLALCHEMY_DATABASE_URI = os.environ['SQLALCHEMY_DATABASE_URI']
    # Application should be dropped environment was not configured.
    # So we use os.environ[] instead of os.environ.get() because it will raise KeyError
    # if SQLALCHEMY_DATABASE_URI is not specified.


class ProductionConfig(Config):
    DEBUG = False


class DevelopmentConfig(Config):
    DEBUG = True  # override Config.DEBUG

    # Show interactive debugger in browser
    DEVELOPMENT = True

    # Database settings
    SQLALCHEMY_TRACK_MODIFICATIONS = False


class TestingConfig(Config):
    TESTING = True
