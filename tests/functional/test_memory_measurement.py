
def test_memory_measurement(prepared_test_client):
    time_limit_code = 'print("hello")'
    submission = dict(problem_id=1,
                      language='python3',
                      src_code=time_limit_code)
    res = prepared_test_client.post('/submissions/submit', json=submission)
    assert res.status_code == 202
    assert res.json is not None
    try:
        for test_results in res.json['submission']['testing_results']['details']:
            memory_usage = test_results['memory_usage']
            assert isinstance(memory_usage, float)
            assert memory_usage > 5   # Code that we used for this test cannot use
            assert memory_usage < 30  # too much memory, but if it does we have problems
    except KeyError:
        assert False
