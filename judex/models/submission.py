from ..database import db


class Submission(db.Model):
    __tablename__ = 'submissions'

    id = db.Column(db.Integer, primary_key=True, autoincrement=True)
    problem_id = db.Column(db.Integer, nullable=False)
    score = db.Column(db.Integer, default=None)

    def __repr__(self):
        return f'<Submission #{self.id}>'
