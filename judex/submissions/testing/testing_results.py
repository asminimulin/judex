import json
import enum

from .submission import SubmissionContext


class TestingResults:

    class Verdict(enum.Enum):
        NotTested = 'NT'
        Running = 'R'
        CompilationError = 'CE'
        PartialSolution = 'PS'
        CompleteSolution = 'CS'

        def __str__(self):
            return str(self.value)

    class TestResults:

        class Verdict(enum.Enum):
            NotExecuted = 'NULL'
            Correct = 'OK'
            WrongAnswer = 'WA'
            RuntimeError = 'RE'
            TimeLimitExceeded = 'TLE'
            MemoryLimitExceeded = 'MLE'

            def __str__(self):
                return str(self.value)

        def __init__(self, test_num):
            self.test_num = test_num
            self.verdict = self.Verdict.NotExecuted
            self.memory_usage = None
            self.running_time = None

        def as_json(self):
            return dict(test_num=self.test_num,
                        verdict=str(self.verdict),
                        memory_usage=self.memory_usage,
                        running_time=self.running_time)

    def __init__(self, submission_context: SubmissionContext, is_initial=True):
        self._results = {'details': [], 'score': 0, 'verdict': str(self.Verdict.NotTested)}
        self._output_file = submission_context.get_result_file()
        self._verdict = self.Verdict.NotTested
        self._score = 0
        self._submission_id = submission_context.submission_id
        if is_initial:
            self.apply()
        else:
            self._results = json.load(open(self._output_file, 'r'))

    def add_test_results(self, test_results: TestResults):
        self._results['details'].append(test_results.as_json())

    def apply(self):
        """Save current testing results in json file self.output_file"""

        with open(self._output_file, 'w') as f:
            json.dump(self._results, indent=True, fp=f)

    def set_testing_result(self, verdict: Verdict):
        self._verdict = verdict
        self._results['verdict'] = str(verdict)

    def score(self, points):
        self._score += points
        self._results['score'] = self._score

    def get_current_score(self):
        return self._score

    def as_json(self):
        return self._results

    def __repr__(self):
        return f'<TestingResults (Submission#{self._submission_id})>'
