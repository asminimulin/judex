from flask import Blueprint
submissions_blueprint = Blueprint('submissions', __name__, url_prefix='/submissions')


from .testing import Tester
submission_tester = Tester(1)


from .routes import *

