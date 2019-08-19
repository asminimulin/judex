#!/usr/bin/python3

import subprocess
import time
import json
import configparser
import resource
import os
from submission_verdict import Verdict
import psutil


class DefaultSubmission:

    def __init__(self, submission):
        self.language = submission['language']
        language_config = configparser.ConfigParser()
        language_config.read(f'/opt/judex/languages/{self.language}/config.ini')
        self.compile_command = language_config[self.language]['compile']
        config = configparser.ConfigParser()
        config.read('/etc/judex/judex.conf')
        self.submission = os.path.join(config['global']['submissions'], str(submission['id']))
        self.problem = os.path.join(config['global']['problems'], str(submission['problem_id']))
        self.source = os.path.join(self.submission, str(submission['id']) + language_config[self.language]['extension'])
        self.compile_log = os.path.join(self.problem, 'compile_log.txt')
        self.problem_config = json.load(open(os.path.join(self.problem, 'problem_config.json'), 'r'))
        self.time_limit = self.problem_config['time']
        self.memory_limit = self.problem_config['memory']
        if self.problem_config['assessment'] == 'by_group':
            self.is_group_passed = [0 for i in range(self.problem_config['group_count'])]
        if language_config[self.language]['type'] == 'compiled':
            self.executable = os.path.join(self.submission, str(submission['id']))
            self.type = 'compiled'
        else:
            self.type = 'interpreted'
        self.verdict = Verdict(os.path.join(self.submission, 'result.json'))

    def check(self):
        print('check_submission')
        if self.type == 'compiled':
            if not self._compile():
                self.verdict.set_status(Verdict.Status.STATUS_CE)
                return
        # TODO:
        # Here we must implement work with permissions for specific UNIX user who will run executable file
        if self.problem_config['assessment'] == 'by_group':
            for group_num in range(self.problem_config['group_count']):
                self._check_group(group_num)
        else:
            for i in range(self.problem_config['first_test'], self.problem_config['last_test'] + 1):
                self._check_test(i)

        if self.verdict.sum == 100:
            self.verdict.set_status(Verdict.Status.STATUS_OK)
        elif self.verdict.sum > 0:
            self.verdict.set_status(Verdict.Status.STATUS_PS)
        else:
            self.verdict.set_status(Verdict.Status.STATUS_LOOSER)
        print ('checked', self.verdict.tests)

    def _compile(self):
        proc = subprocess.Popen([self.compile_command, self.source, self.executable, self.compile_log])
        proc.wait()
        if proc.returncode:
            return False
        return True

    def _check_group(self, group_num):
        group = self.problem_config['groups'][group_num]
        for i in group['required_groups']:
            if not self.is_group_passed[i]:
                self.verdict.add_test(Verdict.Status.STATUS_IGN, 0, 0, count=group['test_count'])
                return  # This groups is unavailable for testing
        last_test = group['last_test']
        full = True
        for test_number in range(group['first_test'], last_test + 1):
            test_passed = self._check_test(test_number)
            if not test_passed:
                if group['assessment'] != 'by_test':
                    self.verdict.add_test(Verdict.Status.STATUS_IGN, 0, 0, count=group['last_test'] - test_number)
                    full = False
                    break
            else:
                self.verdict.score(group['cost'])
        if full:
            self.is_group_passed[group_num] = True
            if group['assessment'] == 'full':
                self.verdict.score(group['cost'])

    def _check_test(self, test_number):
        print('Check test #{}'.format(test_number))

        test_input = os.path.join(self.problem, 'tests/{}'.format(test_number))
        user_output = os.path.join(self.submission, 'output/{}'.format(test_number))
        correct_output = os.path.join(self.problem, 'answers/{}'.format(test_number))
        checker_comment = os.path.join(self.submission, 'comment/{}'.format(test_number))

        test_passed = False
        user_process_memory = 0
        execution_memory = 0
        execution_time = 0.000

        user_process = subprocess.Popen(self.executable,
                                        stdin=open(test_input, 'r'),
                                        stdout=open(user_output, 'w'))

        start_time = time.time()

        try:
            user_process.wait(self.time_limit)
            execution_time = round(time.time() - start_time, 3)
            if user_process.returncode != 0:
                self.verdict.add_test(Verdict.Status.STATUS_RE, execution_time, execution_memory)
                return False
            if user_process_memory > self.memory_limit:
                self.verdict.add_test(Verdict.Status.STATUS_MLE, execution_time, execution_memory)
                return False
        except subprocess.TimeoutExpired:
            execution_time = self.time_limit
            main_process = psutil.Process(user_process.pid)
            for child in main_process.children(recursive=True):
                child.kill()
            main_process.kill()
            self.verdict.add_test(Verdict.Status.STATUS_TLE, execution_time, execution_memory)
            return False
        except MemoryError:
            user_process_memory = self.memory_limit
            self.verdict.add_test(Verdict.Status.STATUS_MLE, execution_time, execution_memory)
            return False
        except:
            self.verdict.add_test(Verdict.Status.STATUS_SV, execution_time, execution_memory)
            return False

        checker_process = subprocess.Popen([os.path.join(self.problem, 'checker'),
                                            test_input, user_output, correct_output, checker_comment])
        checker_process.wait()

        if checker_process.returncode != 0:
            self.verdict.add_test(Verdict.Status.STATUS_WA, execution_time, execution_memory)
            return False
        else:
            self.verdict.add_test(Verdict.Status.STATUS_OK, execution_time, execution_memory)
            return True

    # TODO:
    # Think about implementing it using cgroups
    # def set_memory_limit():
    #    current_limit = resource.getrlimit(resource.RLIMIT_AS)
    #    mb = (1 << 20)
    #    resource.setrlimit(resource.RLIMIT_AS, (800 * mb, current_limit[1]))

if __name__ == '__main__':
    sub = DefaultSubmission({'id': 1, 'problem_id': 1, 'language': 'c++'})
    sub.check()
