from ..database import db


class Problem(db.Model):
    __tablename__ = 'problems'

    id = db.Column(db.Integer, primary_key=True, autoincrement=True)
    name = db.Column(db.String(128), nullable=False, unique=True)

    def __repr__(self):
        return f'<Problem "{self.name}">'
