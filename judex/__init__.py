import os

from flask import Flask

from .database import db


def create_app(test_config=None):
    app = Flask(__name__, instance_relative_config=True)
    app.config.from_object(os.environ.get('APP_SETTINGS') or 'config.ProductionConfig')
    app.config.from_mapping(
        SQLALCHEMY_DATABASE_URI=f'sqlite:///{os.path.join(app.instance_path, "judex.db")}',
        SQLALCHEMY_TRACK_MODIFICATIONS=False
    )

    try:
        os.makedirs(app.instance_path)
    except OSError:
        pass

    db.init_app(app)

    from .models.problem import Problem
    from .models.submission import Submission

    @app.route('/check')
    def check():
        return f'Instance: {app.instance_path}'

    return app

