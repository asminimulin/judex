from datetime import datetime

from ..database import db


class Submission(db.Model):
    __tablename__ = 'submissions'

    id = db.Column(db.Integer, primary_key=True, autoincrement=True)
    problem_id = db.Column(db.Integer, nullable=False)
    score = db.Column(db.Integer, default=None)
    language = db.Column(db.String(64), nullable=False)
    submitted_at = db.Column(db.DateTime, nullable=False, default=datetime.utcnow())
    tested_at = db.Column(db.DateTime, nullable=True, default=None)

    def as_json(self):
        return dict(id=self.id,
                    problem_id=self.problem_id,
                    score=self.score,
                    language=self.language,
                    submitted_at=self.submitted_at,
                    tested_at=self.tested_at)

    def __repr__(self):
        return f'<Submission #{self.id}>'
