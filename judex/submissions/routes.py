import logging
import json

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
        submission_language = request.json['language']
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
        submission_context = SubmissionContext(submission.id, submissions_language=submission_language,
                                               init_fs=True, src_code=src_code)
        testing_submission = TestingSubmission(submission_context, problem_id=submission.problem_id)
    except OSError as e:
        logging.error(f'Problems with internal module "testing", {e}')
        db.session.delete(submission)
        db.session.commit()
        return jsonify({'error': 'Internal error'}), 500
    except ValueError as e:
        logging.error(f'Submission with id={submission.id} used unsupported language={submission_language}')
        db.session.delete(submission)
        db.session.commit()
        return jsonify({'error': str(e)}), 400

    testing_results = submission_tester.test_submission(testing_submission)

    return jsonify({'submission': dict(id=submission.id, testing_results=testing_results.as_json())}), 202
