import pytest
import os


@pytest.mark.parametrize('src_code', [None, "# Some code"])
def test_submit(prepared_test_client, src_code):
    submission_data = {'problem_id': 1, 'language': 'python3'}
    if src_code is not None:
        submission_data['src_code'] = src_code
    response = prepared_test_client.post('/submissions/submit', json=submission_data)
    if 'src_code' not in submission_data or submission_data['src_code'] is None:
        assert response.status_code == 400
        print(response.get_data().decode())
        assert 'Bad submission format' in response.get_data().decode()
    else:
        try:
            assert response.status_code == 202
            assert isinstance(response.json['submission']['id'], int)
        except KeyError:
            raise False


@pytest.mark.parametrize(['src_code', 'lang_name'], [
    ('a, b = map(int, input().split())\n' + 'print(a + b)\n', 'python3'),
    (open(os.path.join(os.path.dirname(__file__), 'src.cpp'), 'r').read(), 'cpp')
])
def test_correct_submission(prepared_test_client, src_code, lang_name):
    submission_data = {'problem_id': 1, 'src_code': src_code, 'language': lang_name}
    response = prepared_test_client.post('/submissions/submit', json=submission_data)
    try:
        assert response.status_code == 202
        assert isinstance(response.json['submission']['id'], int)
        assert response.json['submission']['testing_results']['score'] == 100
        for test in response.json['submission']['testing_results']['details']:
            assert test['verdict'] == 'OK'
    except KeyError:
        assert False


def test_submission_with_compilation_error(prepared_test_client):
    code = 'C++ code to get Compilation Error'
    submission_data = {'problem_id': 1, 'src_code': code, 'language': 'cpp'}
    response = prepared_test_client.post('/submissions/submit', json=submission_data)
    assert response.status_code == 202
    try:
        assert response.json['submission']['testing_results']['verdict'] == 'CE'
        assert response.json['submission']['testing_results']['score'] == 0
        for test in response.json['submission']['testing_results']['details']:
            assert test['verdict'] == 'NULL'
    except KeyError:
        assert False


def test_submit_invalid_language(prepared_test_client):
    code = 'Some code'
    submission_data = {'problem_id': 1, 'src_code': code,
                       'language': 'Some invalid language that user cannot use to submit'}
    response = prepared_test_client.post('/submissions/submit', json=submission_data)
    assert response.status_code == 400
    assert 'Unsupported language' in response.json['error']


def test_submit_bad_problem(prepared_test_client):
    code = 'Some code'
    submission_data = {'problem_id': -1,  # Id = -1 is not a valid problem id for sure
                       'src_code': code, 'language': 'python3'}
    response = prepared_test_client.post('/submissions/submit', json=submission_data)
    assert response.status_code == 400
    assert 'Invalid problem id' in response.json['error']


def test_getting_result_after_time(prepared_test_client):
    code = open(os.path.join(os.path.dirname(__file__), 'src.cpp'), 'r').read()
    submission_data = {'problem_id': 1,
                       'src_code': code,
                       'language': 'cpp'}
    response = prepared_test_client.post('/submissions/submit', json=submission_data)
    assert response.status_code == 202
    submission_id = response.json["submission"]["id"]
    print(submission_id)
    testing_results_immediately = response.json['submission']['testing_results']
    response = prepared_test_client.get(f'/submissions/{submission_id}/results', json={'need_details': True})
    assert response.status_code == 200
    assert response.json['testing_results'] == testing_results_immediately

