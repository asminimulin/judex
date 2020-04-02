import os

from flask import Flask

def create_app(test_config=None):
    app = Flask(__name__, instance_relative_config=True)
    app.config.from_object(os.environ.get('APP_SETTINGS') or 'config.ProductionConfig')
    
    try:
        os.makedirs(app.instance_path)
    except OSError:
        pass

    @app.route('/check')
    def hello():
        return f'Instance: {app.instance_path}'

    return app

