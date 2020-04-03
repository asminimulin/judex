import pytest


@pytest.mark.parametrize('src_code', [None, "# Some code"])
def test_submit(test_client, init_database, src_code):
    submission_data = {'problem_id': 1}
    if src_code is not None:
        submission_data['src_code'] = src_code
    response = test_client.post('/submissions/submit', json=submission_data)
    if 'src_code' not in submission_data or submission_data['src_code'] is None:
        assert response.status_code == 400
        print(response.get_data().decode())
        assert 'Bad submission format' in response.get_data().decode()
    else:
        assert response.status_code == 202
        assert 'submission' in response.json
        assert 'id' in response.json['submission']
        assert isinstance(response.json['submission']['id'], int)

