import logging

from flask import request, jsonify

from ..database import db
from ..models.submission import Submission

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

    print(f'{submission.id=}')

    return jsonify({'submission': {'id': submission.id}}), 202
