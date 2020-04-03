from flask import Blueprint


submissions_blueprint = Blueprint('submissions', __name__, url_prefix='/submissions')


from .routes import *
