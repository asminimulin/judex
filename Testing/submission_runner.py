#!/usr/bin/python3

import subprocess
import time
import json
from common import *
import configparser
import os
from submission_verdict import Verdict
import psutil

class SubmissionRunner:

    def __init_config(self):
        lang_conf_path = os.path.join(JUDEX_HOME, 'conf.d', 'language.conf')
        self.lang_conf = configparser.ConfigParser(os.environ, interpolation=configparser.ExtendedInterpolation())
        self.lang_conf.read(lang_conf_path)
        self.config = configparser.ConfigParser()
        self.config.read(os.path.join(JUDEX_HOME, 'conf.d', 'judex.conf'))

    def __init__(self):
        self.__init_config()
        self.submissions = self.config['testing']['submissions_dir']
        self.problems = self.config['archive']['archive_dir']

    def set_memory_limit(self):
        current_limit = resource.getrlimit(resource.RLIMIT_AS)
        mb = (1 << 20)
        resource.setrlimit(resource.RLIMIT_AS, (800 * mb, current_limit[1]))

    def __load_problem_conf(self):
        problem_conf_path = os.path.join(self.problem_path, 'problem_conf.json')
        self.problem_conf = json.load(open(problem_conf_path, 'r'))

    def __compile(self):
        proc = subprocess.Popen([self.lang_conf[self.language]['compile'], self.code_path, self.exe_path], stderr=open(self.log_path, 'w'))
        proc.wait()
        if proc.returncode:
            return False
        return True

    def __check_test(self, test_number):
        print('Check test #{}'.format(test_number))

        test_input = os.path.join(self.problem_path, 'tests', '{}'.format(test_number))
        user_output = os.path.join(self.submission_path, 'output', '{}'.format(test_number))
        correct_output = os.path.join(self.problem_path, 'answers', '{}'.format(test_number))

        test_passed = False
        user_process_memory = 0
        execution_memory = 0
        execution_time = 0.000
        
        run_array = None
        if self.lang_conf[self.language]['type'] == 'compiled':
            run_array = [self.exe_path]
        user_process = subprocess.Popen(run_array,
                                        stdin=open(test_input, 'r'),
                                        stdout=open(user_output, 'w'))

        start_time = time.time()

        try:
            user_process.wait(self.time_limit)
            execution_time = round(time.time() - start_time, 3)

            if user_process.returncode != 0:
                return (False, Verdict.Status.STATUS_RE, execution_time, execution_memory)

            if user_process_memory > self.memory_limit:
                return (False, Veridct.Status.STATUS_MLE, execution_time, execution_memory)
        except subprocess.TimeoutExpired:
            print('TL caught')
            execution_time = self.time_limit

            main_process = psutil.Process(user_process.pid)
            for child in main_process.children(recursive=True):
                child.kill()
            main_process.kill()

            return (False, Verdict.Status.STATUS_TLE, execution_time, execution_memory)
        except MemoryError:
            user_process_memory = self.memory_limit
            return (False, Verdict.Status.STATUS_MLE, execution_time, execution_memory)
        except:
            return (False, Verdict.Status.STATUS_SV, execution_time, execution_memory)

        checker_process = subprocess.Popen([os.path.join(self.problem_path, 'checker'),
                                            test_input, user_output, correct_output])
        checker_process.wait()

        verdict_status = None
        test_passed = None

        if checker_process.returncode != 0:
            verdict_status = Verdict.Status.STATUS_WA
            test_passed = False
        else:
            verdict_status = Verdict.Status.STATUS_OK
            test_passed = True

        return (test_passed, verdict_status, execution_time, execution_memory)

    # Check tests_group
    def __check_group(self, first_test_number, group, verdict):
        is_group_available = True
        for req_grp in group['required']:
            if not self.passed_groups[req_grp - 1]:
                is_group_available = False
                break
        tests_count = group['tests_count']
        last_test = first_test_number + tests_count - 1
        if not is_group_available:
            verdict.add_test(Verdict.Status.STATUS_IGN, 0, 0, count=tests_count)
            return test_count
        full_passed = True
        for test_number in range(first_test_number, last_test + 1):
            test_passed, execution_status, execution_time, execution_memory = \
                                                                self.__check_test(test_number)
            if test_passed:
                verdict.add_test(execution_status, execution_time, execution_memory)
                print('test #{} passed scoring'.format(test_number))
                if group['assesment'] == 'by_test':
                    verdict.score(group['cost'])
            else:
                full_passed = False
                verdict.add_test(execution_status, execution_time, execution_memory)
                verdict.add_test(Verdict.Status.STATUS_IGN, 0, 0, last_test - test_number)
                break

        if full_passed:
            self.passed_groups.append(True)
            if group['assesment'] == 'full':
                verdict.score(group['score'])
        else:
            self.passed_groups.append(False)
        return tests_count

    def check_submission(self, submission_id, problem_id, language):
        self.total_tests_passed = 0
        submission_path = os.path.join(self.submissions, str(submission_id))
        if not os.path.exists(submission_path) or not os.path.isdir(submission_path):
            raise Exception('Bad submission id')

        problem_path = os.path.join(self.problems, str(problem_id))
        if not os.path.exists(problem_path) or not os.path.isdir(problem_path):
            raise Exception('Bad problem id')

        if not language in self.lang_conf:
            raise Exception('Bad language')
        
        self.submission_id = submission_id
        self.problem_id = problem_id
        self.language = language
        self.submission_path = submission_path
        self.problem_path = problem_path
        result_file = os.path.join(self.submission_path, 'result.json')
        verdict = Verdict(result_file)
      
        self.__load_problem_conf()
        self.time_limit = self.problem_conf['time']
        self.memory_limit = self.problem_conf['memory']

        print('time limit = {}s'.format(self.time_limit))
        print('memory limit = {}B'.format(self.memory_limit))
        
        self.code_path = os.path.join(submission_path, '{}{}'.format(submission_id, self.lang_conf[self.language]['extension']))
        self.exe_path = None

        if self.lang_conf[language]['type'] == 'compiled':
            self.exe_path = os.path.join(submission_path, '{}'.format(submission_id))
            self.log_path = os.path.join(submission_path, 'log')
            if not self.__compile():
                verdict.set_status(Verdict.Status.STATUS_CE)
                return
        else:
            self.exe_path = code_path

        problem_conf_path = os.path.join(problem_path, 'problem_conf.json')
        problem_conf = json.load(open(problem_conf_path, 'r'))
        self.passed_groups = [0] 
        first_test = 1
        for group in problem_conf['groups']:
            first_test += self.__check_group(first_test, group, verdict)

        if verdict.sum == 100:
            verdict.set_status(Verdict.Status.STATUS_OK)
        elif verdict.sum > 0:
            verdict.set_status(Verdict.Status.STATUS_PS)
        else:
            verdict.set_status(Verdict.Status.STATUS_LOOSER)

if __name__ == '__main__':
    runner = SolutionRunner('/tmp/judex')
    print('Check first submission')
    runner.check_submission(1, 1, 'c++')
    print('Check second submission')
    runner.check_submission(2, 1, 'c++')
