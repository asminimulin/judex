import os
import logging

from flask import current_app as app

from .language import LanguageHelper
from .problem import Problem, ProblemContext


class SubmissionContext:
    TESTING_RESULTS_FILENAME = 'result.json'
    TESTS_OUTPUT_DIRECTORY_NAME = 'Output'
    SUBMISSIONS_DIRECTORY_NAME = 'Submissions'
    COMPILER_OUTPUT_FILENAME = 'compiler_output.txt'
    COMPILED_EXECUTABLE_FILENAME = 'executable'

    def __init__(self, submission_id: int, submissions_language: str, init_fs: bool = False, src_code: str = None):
        # Directory where all submission's files would be stored
        self.path = os.path.join(app.instance_path, SubmissionContext.SUBMISSIONS_DIRECTORY_NAME, str(submission_id))

        self.language_helper = LanguageHelper(submissions_language)

        self._source_path = os.path.join(self.path, f'{submission_id}{self.language_helper.extension}')

        # Directory where tester stores output of every test
        self._output_directory = os.path.join(self.path, SubmissionContext.TESTS_OUTPUT_DIRECTORY_NAME)

        # File where tester save testing results in json format
        self._result_file = os.path.join(self.path, SubmissionContext.TESTING_RESULTS_FILENAME)

        self._compiler_output_file = os.path.join(self.path, SubmissionContext.COMPILER_OUTPUT_FILENAME)

        if init_fs:
            self.init_fs(src_code)

        self.submission_id = submission_id

    def init_fs(self, src_code):
        os.makedirs(self.path)
        os.makedirs(self._output_directory)
        assert src_code is not None
        with open(self._source_path, 'w') as f:
            f.write(src_code)

    def get_result_file(self) -> str:
        return self._result_file

    def get_output_directory(self) -> str:
        return self._output_directory

    def get_source(self) -> str:
        return self._source_path

    def get_compiler_output_file(self) -> str:
        return self._compiler_output_file

    def get_executable_file(self) -> str:
        if self.language_helper.is_compiled:
            return os.path.join(self.path, SubmissionContext.COMPILED_EXECUTABLE_FILENAME)
        else:
            return os.path.join(self.path, self.language_helper.interpreter_path)

    def __repr__(self):
        return f'<SubmissionContext (Submission#{self.submission_id})>'


class Submission:
    def __init__(self, submission_context: SubmissionContext, problem_id: int):
        self._submission_context = submission_context
        self.problem = Problem(ProblemContext(problem_id))
        self.id = submission_context.submission_id

    def get_submission_context(self) -> SubmissionContext:
        return self._submission_context

    def __repr__(self):
        return f'<Submission#{self.id}>'
