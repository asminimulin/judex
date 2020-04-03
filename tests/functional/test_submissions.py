import pytest


def test_submit(test_client, init_database):
    submission_data = {'problem_id': 1}
    response = test_client.post('/submissions/submit', json=submission_data)
    assert response.status_code == 202
    assert 'submission' in response.json
    assert 'id' in response.json['submission']
    assert isinstance(response.json['submission']['id'], int)
