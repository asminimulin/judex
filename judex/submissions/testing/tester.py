import os
import subprocess
import logging
from threading import Lock
import time
import psutil

from .testing_results import TestingResults
from .submission import Submission
from .problem import Problem, ProblemContext


class Tester:
    # FIXME: This is not how it is really work in most cases, but till now it is not the core feature
    TEST_COST = 1

    # FIXME: only linux specific, need to make it crossplatform
    OUTPUT_EATER = '/dev/null'

    # The problem which is testing with current submission
    problem: Problem

    # The directory where submission's output will be stored
    output_directory: str

    # Currently testing submission
    submission: Submission

    def __init__(self, id):
        self.id = id
        self.lock = Lock()

    def test_submission(self, submission: Submission):
        logging.info(f'Start testing {submission}')
        with self.lock:
            logging.info(f'Tester {self.id} took {submission}')

            self.submission = submission
            context = submission.get_submission_context()
            self.problem = submission.problem
            self.output_directory = context.get_output_directory()

            testing_results = TestingResults(context)

            if context.language_helper.is_compiled:
                success = context.language_helper.compile(context)
                if not success:
                    testing_results.set_testing_result(testing_results.Verdict.CompilationError)
                    for i in range(1, self.problem.tests_count + 1):
                        testing_results.add_test_results(TestingResults.TestResults(i))
                    testing_results.apply()
                    return testing_results

            testing_results.set_testing_result(testing_results.Verdict.Running)
            testing_results.apply()

            for test_number in range(1, self.problem.tests_count + 1):
                test_results = self.run_test(test_number)
                testing_results.add_test_results(test_results)
                if test_results.verdict == test_results.Verdict.Correct:
                    testing_results.score(Tester.TEST_COST)
                if test_number % 10 == 0:
                    testing_results.apply()

            logging.info(f'Finish testing {submission}')
            if testing_results.get_current_score() == self.problem.tests_count:
                testing_results.set_testing_result(TestingResults.Verdict.CompleteSolution)
            else:
                testing_results.set_testing_result(TestingResults.Verdict.PartialSolution)
            testing_results.apply()
            return testing_results

    def run_test(self, test_number) -> TestingResults.TestResults:
        logging.info(f'Run test test_number={test_number}')
        test_results = TestingResults.TestResults(test_number)
        input_file = self.problem.get_test(test_number)
        output_file = os.path.join(self.output_directory, f'{test_number}.txt')
        try:
            if self.submission.get_submission_context().language_helper.is_compiled:
                args = [self.submission.get_submission_context().get_executable_file()]
            else:
                args = [self.submission.get_submission_context().language_helper.interpreter_path,
                        self.submission.get_submission_context().get_source()]
            process = subprocess.Popen(args,
                                       stdin=open(input_file, 'r'),
                                       stdout=open(output_file, 'w'),
                                       stderr=open(Tester.OUTPUT_EATER, 'w')
                                       )
            started_at = time.time()
            os_process = psutil.Process(process.pid)
            memory_usage = 0.0
            while time.time() - started_at < self.problem.time_limit and process.poll() is None:
                current_memory_usage = os_process.memory_info().rss
                current_memory_usage = current_memory_usage / 1024 / 1024  # convert to MB
                memory_usage = max(memory_usage, current_memory_usage)
                test_results.memory_usage = memory_usage

            if process.poll() is None:
                process.kill()

            running_time = time.time() - started_at
            test_results.running_time = running_time

            if running_time > self.problem.time_limit:
                raise TimeoutError

        except TimeoutError:
            test_results.verdict = test_results.Verdict.TimeLimitExceeded
            return test_results

        if process.returncode != 0:
            test_results.verdict = test_results.Verdict.RuntimeError
            return test_results

        checker = self.problem.get_checker()

        stderr = open(Tester.OUTPUT_EATER, 'w')
        process = subprocess.run([checker, input_file, output_file],
                                 # executable=checker,
                                 stderr=stderr,
                                 stdout=stderr)
        if process.returncode != 0:
            test_results.verdict = test_results.Verdict.WrongAnswer
        else:
            test_results.verdict = test_results.Verdict.Correct

        return test_results
