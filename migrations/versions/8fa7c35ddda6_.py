"""empty message

Revision ID: 8fa7c35ddda6
Revises: d4fd6d6a9467
Create Date: 2020-04-11 19:45:22.025078

"""
from alembic import op
import sqlalchemy as sa


# revision identifiers, used by Alembic.
revision = '8fa7c35ddda6'
down_revision = 'd4fd6d6a9467'
branch_labels = None
depends_on = None


def upgrade():
    # ### commands auto generated by Alembic - please adjust! ###
    op.create_table('problems',
    sa.Column('id', sa.Integer(), autoincrement=True, nullable=False),
    sa.Column('name', sa.String(length=128), nullable=False),
    sa.PrimaryKeyConstraint('id'),
    sa.UniqueConstraint('name')
    )
    op.create_table('submissions',
    sa.Column('id', sa.Integer(), autoincrement=True, nullable=False),
    sa.Column('problem_id', sa.Integer(), nullable=False),
    sa.Column('score', sa.Integer(), nullable=True),
    sa.Column('language', sa.String(length=64), nullable=False),
    sa.Column('submitted_at', sa.DateTime(), nullable=False),
    sa.Column('tested_at', sa.DateTime(), nullable=True),
    sa.PrimaryKeyConstraint('id')
    )
    # ### end Alembic commands ###


def downgrade():
    # ### commands auto generated by Alembic - please adjust! ###
    op.drop_table('submissions')
    op.drop_table('problems')
    # ### end Alembic commands ###
