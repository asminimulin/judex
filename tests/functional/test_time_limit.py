import pytest
from judex.submissions.testing.testing_results import TestingResults


def test_time_limit(prepared_test_client):
    time_limit_code = 'while(True): pass'
    submission = dict(problem_id=1,
                      language='python3',
                      src_code=time_limit_code)
    res = prepared_test_client.post('/submissions/submit', json=submission)
    assert res.status_code == 202
    assert res.json is not None
    try:
        assert res.json['submission']['testing_results']['score'] == 0
        for test_results in res.json['submission']['testing_results']['details']:
            assert test_results['verdict'] == str(TestingResults.TestResults.Verdict.TimeLimitExceeded)
    except KeyError:
        assert False
