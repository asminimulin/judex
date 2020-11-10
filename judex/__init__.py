import os
import logging

from flask import Flask, render_template

from .database import db


def create_app(specific_config=None):
    app = Flask(__name__, instance_relative_config=True)
    app.config.from_object(os.environ['APP_CONFIG_OBJECT'])

    if specific_config:
        app.config.from_mapping(specific_config)

    try:
        os.makedirs(app.instance_path)
    except OSError:
        # It's OK, it means directory is already exists
        pass

    db.init_app(app)

    from .models.problem import Problem
    from .models.submission import Submission

    from .submissions import submissions_blueprint

    app.register_blueprint(submissions_blueprint)

    @app.route('/check')
    def check():
        return f'Instance: {app.instance_path}'

    @app.route('/')
    def index():
        return render_template('index.html')

    if 'DEBUG' in app.config and app.config['DEBUG']:
        logging.basicConfig(level=logging.DEBUG)

    if 'DEVELOPMENT' in app.config and app.config['DEVELOPMENT']:
        logging.basicConfig(level=logging.NOTSET)

    return app

