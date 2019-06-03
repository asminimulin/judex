#!/usr/bin/python3

import subprocess
import time
import json
from common import *
import configparser
import os

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
        run_array = None
        if self.lang_conf[self.language]['type'] == 'compiled':
            run_array = [self.exe_path]
        user_process = subprocess.Popen(run_array,
                                        stdin=open(test_input, 'r'),
                                        stdout=open(user_output, 'w'),
        )
        user_process_memory = 0
        time_of_execution = 0.000
        try:
            start_time = time.time()
            user_process.wait(self.time_limit)
            time_of_execution = round(time.time() - start_time, 3)
            verdict = ';{};{}'.format(time_of_execution, min(user_process_memory, self.memory_limit))
            if user_process.returncode != 0:
                verdict = 'RE' + verdict
            if user_process_memory > self.memory_limit:
                user_process_memory = self.memory_limit
                verdict = 'ML;{};{}'.format(time_of_execution, self.memory_limit)
        except subprocess.TimeoutExpired:
            time_of_execution = self.time_limit
            user_process_memory = self.memory_limit
            verdict = 'TL;{};{}'.format(self.time_limit, user_process_memory)
            main_process = psutil.Process(user_process.pid)
            for child in main_process.children(recursive=True):
                child.kill()
            main_process.kill()
        except MemoryError:
            user_process_memory = self.memory_limit
            verdict = 'ML;{};{}'.format(time_of_execution, self.memory_limit)
        except:
            verdict = 'NLSP;42;42'

        if verdict[0] != ';':
            self.result['tests'].append(verdict)
        else:
            checker_process = subprocess.Popen([os.path.join(self.problem_path, 'checker'),
                                                test_input, user_output, correct_output])
            checker_process.wait()
            if checker_process.returncode != 0:
                verdict = 'WA' + verdict
                self.result['tests'].append(verdict)
            else:
                verdict = 'OK' + verdict
                self.result['tests'].append(verdict)
                test_passed = True
        '''#self.max_memory = max(min(user_process_memory, self.memory_limit), self.max_memory)
        self.max_time = max(min(time_of_execution, self.time_limit), self.max_time)
        #self.average_memory += min(user_process_memory, self.memory_limit)
        self.average_time += min(time_of_execution, self.time_limit)
        '''
        self.max_memory = 0
        self.average_memory = 0
        self.max_time = 0
        self.average_time = 0
        ret = (test_passed, verdict)
        return ret

    # Check group of test
    def __check_group(self, first_test_number, group):
        is_group_available = True
        for req_grp in group['required']:
            if not self.passed_groups[req_grp - 1]:
                is_group_available = False
                break
        tests_amount = group['tests_count']
        if not is_group_available:
            self.result['tests'] += ['IGN;0;0'] * tests_amount
            return 0
        passed_tests_amount = 0
        for test_number in range(first_test_number, first_test_number + tests_amount):
            current_result = self.__check_test(test_number)
            test_passed = current_result[0]
            if test_passed:
                passed_tests_amount += 1
                self.result['tests_passed'] += 1
                if group['assesment'] == 'by_test':
                    self.result['sum'] += group['cost']
            self.result['status'] = "RUN " + str(test_number)
            with open(self.result_file, 'w') as f:
                json.dump(self.result, f)
                if not test_passed and group['assesment'] == 'full':
                    self.result['status'] = current_result[1].split(';')[0]
                    self.result['tests'] += \
                        ['IGN;0;0'] * (first_test_number + tests_amount - test_number - 1)
                    json.dump(self.result, f)
                    break

        if passed_tests_amount == tests_amount:
            self.passed_groups.append(True)
        else:
            self.passed_groups.append(False)
        return tests_amount

    def check_submission(self, submission_id, problem_id, language):
        self.result = {}
        self.result['tests'] = []
        self.result['tests_passed'] = 0
        self.result['sum'] = 0
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
        self.result_file = os.path.join(self.submission_path, 'result.json')
      
        self.__load_problem_conf()
        self.time_limit = self.problem_conf['time']
        self.memory_limit = self.problem_conf['memory']

        print(self.time_limit)
        print(self.memory_limit)
        
        self.code_path = os.path.join(submission_path, '{}{}'.format(submission_id, self.lang_conf[self.language]['extension']))
        self.exe_path = None

        if self.lang_conf[language]['type'] == 'compiled':
            self.exe_path = os.path.join(submission_path, '{}'.format(submission_id))
            self.log_path = os.path.join(submission_path, 'log')
            if not self.__compile():
                return
        else:
            self.exe_path = code_path

        problem_conf_path = os.path.join(problem_path, 'problem_conf.json')
        problem_conf = json.load(open(problem_conf_path, 'r'))
        self.passed_groups = [0] 
        first_test = 1
        for group in problem_conf['groups']:
            first_test += self.__check_group(first_test, group)

if __name__ == '__main__':
    runner = SolutionRunner('/tmp/judex')
    print('Check first submission')
    runner.check_submission(1, 1, 'c++')
    print('Check second submission')
    runner.check_submission(2, 1, 'c++')
