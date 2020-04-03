import pytest

from judex import create_app
from judex import db

from judex.models.problem import Problem


@pytest.fixture(scope='module')
def new_problem():
    problem = Problem(name='Test problem')
    return problem


@pytest.fixture(scope='module')
def test_client():
    flask_app = create_app({
        'TESTING': True
    })

    # Flask provides a way to test your application by exposing the Werkzeug test Client
    # and handling the context locals for you.
    testing_client = flask_app.test_client()

    # Establish an application context before running the tests.
    ctx = flask_app.app_context()
    ctx.push()

    yield testing_client  # this is where the testing happens!

    ctx.pop()


@pytest.fixture(scope='module')
def init_database():
    # Give fixture info about used models to create tables in db
    # noinspection PyUnresolvedReferences
    import judex.models.submission
    # noinspection PyUnresolvedReferences
    import judex.models.problem

    db.create_all()
    db.session.commit()

    yield db

    db.drop_all()
    db.session.commit()
