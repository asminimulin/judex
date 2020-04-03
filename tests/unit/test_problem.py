from judex.models.problem import Problem


def test_new_problem(new_problem):
    assert new_problem.name == 'Test problem'
