import pytest
import os


@pytest.mark.parametrize('src_code', [None, "# Some code"])
def test_submit(test_client, init_database, init_archive, src_code):
    submission_data = {'problem_id': 1, 'language': 'python3'}
    if src_code is not None:
        submission_data['src_code'] = src_code
    response = test_client.post('/submissions/submit', json=submission_data)
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
def test_correct_submission(test_client, init_database, init_archive, src_code, lang_name):
    submission_data = {'problem_id': 1, 'src_code': src_code, 'language': lang_name}
    response = test_client.post('/submissions/submit', json=submission_data)
    try:
        assert response.status_code == 202
        assert isinstance(response.json['submission']['id'], int)
        assert response.json['submission']['testing_results']['score'] == 100
        for test in response.json['submission']['testing_results']['details']:
            assert test['verdict'] == 'OK'
    except KeyError:
        assert False


def test_submission_with_compilation_error(system_ready):
    code = 'C++ code to get Compilation Error'
    submission_data = {'problem_id': 1, 'src_code': code, 'language': 'cpp'}
    response = system_ready.post('/submissions/submit', json=submission_data)
    assert response.status_code == 202
    try:
        assert response.json['submission']['testing_results']['verdict'] == 'CE'
        assert response.json['submission']['testing_results']['score'] == 0
        for test in response.json['submission']['testing_results']['details']:
            assert test['verdict'] == 'NULL'
    except KeyError:
        assert False


def test_submit_invalid_language(system_ready):
    code = 'Some code'
    submission_data = {'problem_id': 1, 'src_code': code,
                       'language': 'Some invalid language that user cannot use to submit'}
    response = system_ready.post('/submissions/submit', json=submission_data)
    assert response.status_code == 400
    assert 'Unsupported language' in response.json['error']


def test_submit_bad_problem(system_ready):
    code = 'Some code'
    submission_data = {'problem_id': -1,  # Id = -1 is not a valid problem id for sure
                       'src_code': code, 'language': 'python3'}
    response = system_ready.post('/submissions/submit', json=submission_data)
    assert response.status_code == 400
    assert 'Invalid problem id' in response.json['error']
