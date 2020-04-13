from judex.submissions.testing.testing_results import TestingResults
MemoryLimitExceeded = TestingResults.TestResults.Verdict.MemoryLimitExceeded


def test_time_limit(prepared_test_client):
    time_limit_code = '#include <vector>\n'\
                      '#include <algorithm>\n'\
                      '#include <iostream>\n'\
                      'int main() {' \
                      'std::vector<long long> a(9 * 1000 * 1000);' \
                      'std::reverse(a.begin(), a.end());' \
                      '}'
    submission = dict(problem_id=1,
                      language='cpp',
                      src_code=time_limit_code)
    res = prepared_test_client.post('/submissions/submit', json=submission)
    assert res.status_code == 202
    assert res.json is not None
    try:
        assert res.json['submission']['testing_results']['score'] == 0
        for test_results in res.json['submission']['testing_results']['details']:
            assert test_results['verdict'] == str(MemoryLimitExceeded)
    except KeyError:
        assert False
