import os
import json

from flask import current_app as app


class ProblemContext:
    PROBLEMS_DIRECTORY_NAME = 'Problems'
    PROBLEM_CONFIG_NAME = 'config.json'
    PROBLEM_TESTS_DIRECTORY_NAME = 'Tests'
    PROBLEM_CHECKER_NAME = 'checker'

    def __init__(self, problem_id: int):
        self.problem_id = problem_id

        # Path to directory where problem's files are
        self.path = os.path.join(app.instance_path, ProblemContext.PROBLEMS_DIRECTORY_NAME, str(problem_id))

        # Problem's checker executable file path
        self.checker_path = os.path.join(self.path, ProblemContext.PROBLEM_CHECKER_NAME)

        # Problem's config file in json format
        self._config_path = os.path.join(self.path, ProblemContext.PROBLEM_CONFIG_NAME)

        # Problem's tests directory
        self._tests_directory = os.path.join(self.path, ProblemContext.PROBLEM_TESTS_DIRECTORY_NAME)

    def get_config_path(self) -> str:
        return self._config_path

    def get_test_directory(self) -> str:
        return os.path.join(self._tests_directory)


class Problem:

    def __init__(self, problem_context: ProblemContext):
        self.id = problem_context.problem_id
        self._problem_context = problem_context
        try:
            self.config = json.load(open(problem_context.get_config_path(), 'r'))
        except FileNotFoundError:
            raise ValueError('Invalid problem id')

        self.tests_count = self.config['tests_count']

    def get_test(self, test_num: int) -> str:
        if test_num not in range(1, self.config['tests_count'] + 1):
            raise IndexError(f'test_num must be in range(1, {self.config["tests_count"]})')
        return os.path.join(self._problem_context.get_test_directory(), f'{test_num}.txt')

    def get_checker(self) -> str:
        return self._problem_context.checker_path
