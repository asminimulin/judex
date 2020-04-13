import logging

from flask import request, jsonify

from .testing.submission import SubmissionContext
from ..database import db
from ..models.submission import Submission
from .testing import Submission as TestingSubmission, TestingResults
from . import submission_tester

from . import submissions_blueprint


@submissions_blueprint.route('/submit', methods=['POST'])
def submit():
    if request.json is None:
        return 'Request needs to contain form data', 400

    try:
        submission_language = request.json['language']
        submission = Submission(problem_id=request.json['problem_id'],
                                language=submission_language)
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
        submission_context = SubmissionContext(submission.id, submissions_language=submission_language,
                                               init_fs=True, src_code=src_code)
        testing_submission = TestingSubmission(submission_context, problem_id=submission.problem_id)
    except OSError as e:
        logging.error(f'Problems with internal module "testing", {e}')
        return jsonify({'error': 'Internal error'}), 500
    except ValueError as e:
        logging.error(f'Submission with id={submission.id} used unsupported language={submission_language}')
        return jsonify({'error': str(e)}), 400

    testing_results = submission_tester.test_submission(testing_submission)

    submission.score = testing_results.get_current_score()
    db.session.commit()

    return jsonify({'submission': dict(id=submission.id, testing_results=testing_results.as_json())}), 202


@submissions_blueprint.route('<int:submission_id>/results', methods=['GET'])
def get_submission_results(submission_id):
    submission = Submission.query.get(submission_id)
    if not submission:
        return 'No such submission', 404
    need_testing_details = request.json is not None and \
                           'need_details' in request.json and \
                           request.json['need_details'] is True
    logging.info(f'requested testing results of {submission}')
    json = submission.as_json()
    if need_testing_details:
        submission_context = SubmissionContext(submission.id, submissions_language=submission.language, init_fs=False)
        testing_results = TestingResults(submission_context, is_initial=False)
        json['testing_results'] = testing_results.as_json()
    return jsonify(json), 200
