import os
import logging

from flask import Flask

from .database import db


def create_app(test_config=None):
    app = Flask(__name__, instance_relative_config=True)
    app.config.from_mapping(
        SQLALCHEMY_DATABASE_URI=f'sqlite:///{os.path.join(app.instance_path, "judex.db")}',
        SQLALCHEMY_TRACK_MODIFICATIONS=False
    )
    app.config.from_object(os.environ.get('APP_SETTINGS') or 'config.ProductionConfig')

    if test_config:
        app.config.from_mapping(test_config)

    try:
        os.makedirs(app.instance_path)
    except OSError:
        pass

    db.init_app(app)

    from .models.problem import Problem
    from .models.submission import Submission

    from . import submissions

    app.register_blueprint(submissions.submissions_blueprint)

    @app.route('/check')
    def check():
        return f'Instance: {app.instance_path}'

    if 'DEVELOPMENT' in app.config and app.config['DEVELOPMENT']:
        logging.basicConfig(level=logging.DEBUG)

    logging.basicConfig(level=logging.DEBUG)

    return app

