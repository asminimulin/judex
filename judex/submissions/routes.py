import logging

from flask import request, jsonify

from .testing.submission import SubmissionContext
from ..database import db
from ..models.submission import Submission
from .testing import Submission as TestingSubmission
from . import submission_tester

from . import submissions_blueprint


@submissions_blueprint.route('/submit', methods=['POST'])
def submit():
    if request.json is None:
        return 'Request needs to contain form data', 400

    try:
        submission = Submission(problem_id=request.json['problem_id'])
        src_code = request.json['src_code']
        if src_code is None:
            raise ValueError
    except (KeyError, ValueError):
        return 'Bad submission format', 400

    try:
        db.session.add(submission)
        db.session.commit()

        # Load autoincrement column id of new submission
        db.session.refresh(submission)
    except Exception as e:
        logging.debug(f'Unexpected database error {e}')
        raise e

    try:
        submission_context = SubmissionContext(submission.id, init_fs=True, src_code=src_code)
    except OSError:
        db.session.delete(submission)
        db.session.commit()
        logging.error('Problems with internal module testing, failed to create submission context')
        return 'Some troubles with internal component', 500

    testing_submission = TestingSubmission(submission_context, problem_id=submission.problem_id)
    submission_tester.test_submission(testing_submission)

    return jsonify({'submission': {'id': submission.id}}), 202
