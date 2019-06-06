#!/usr/bin/python3
import json
import os
import subprocess
import sys
import time


def check_file(global_path, number, name):
    try:
        f = open(os.path.join(global_path, name, str(number)), 'r')
        return True
    except Exception:
        return False
    
    
def check_solution_on_test(global_path, number, time_limit):
    buffer = os.path.join(global_path, str(number))
    process = subprocess.Popen([os.path.join(global_path, 'solution')], stdin=open(os.path.join(global_path, 'tests', str(number)), 'r'),
                               stdout=open(buffer, 'w'))
    time.sleep(time_limit)
    code = process.returncode
    print('Processing test {}'.format(str(number)))
    if open(buffer, 'r').read() != open(os.path.join(global_path, 'answers', str(number)), 'r').read():
        return 1
    try:
        process.kill()
        return 2
    except Exception:
        if code is not None and code != 0:
            return 2
    os.remove(buffer)
    return 0


def check_directory(global_path, files_amount, name):
    if not os.path.exists(os.path.join(global_path, name)):
        verdict = 'No {} directory!'.format(name)
        return {'check': 'failed', 'verdict': verdict}
    no_files = []
    for i in range(1, files_amount + 1):
        if not check_file(global_path, i, name):
            no_files.append(i)
    if len(no_files) == 0:
        return {'check': 'OK', 'verdict': 'Everything with {} is OK!'.format(name)}
    elif len(no_files) == files_amount:
        verdict = 'There are no {} at all!'.format(name)
        return {'check': 'failed', 'verdict': verdict}
    else:
        verdict = 'There are no {} number '.format(name) + ' '.join([str(x) for x in no_files]) + '!'
        return {'check': 'failed', 'verdict': verdict}
    
    
def check_solution(global_path, files_amount, time_limit):
    re_tests = []
    wa_tests = []
    for i in range(1, files_amount + 1):
        ans = check_solution_on_test(global_path, i, time_limit)
        if ans == 1:
            wa_tests.append(i)
        elif ans == 2:
            re_tests.append(i)
    if len(re_tests) + len(wa_tests) == 0:
        return {'check': 'OK', 'verdict': 'Everything with solution is OK!'}
    elif len(re_tests) != 0:
        return {'check': 'failed', 'verdict': 'Solution got runtime-error on tests ' + ' '.join([str(x) for x in re_tests]) + '!'}
    else:
        return {'check': 'OK', 'verdict': 'There are different answers on tests ' + ' '.join([str(x) for x in wa_tests]) + '!'}
        
    
commands = sys.argv[1:]
if len(commands) < 2:
    print('Please read readme.txt to learn how to use the script.')
    exit(2)    
global_path = commands[0]
mode = commands[1]
try:
    conf = json.load(open(os.path.join(global_path, 'problem_conf.json'), 'r'))
except FileExistsError:
    print('Problem directory is not right! No file named "problem_conf"!')
    exit(2)
try:
    groups = conf['groups']
except Exception:
    print('Problem configuration is not right! No field named "groups"!')
    exit(2)
try:
    time_limit = float(conf['time'])
except Exception:
    print('Problem configuration is not right! No field named "time"!')
    exit(2) 
files_amount = 0
for elem in groups:
    files_amount += elem['tests_count']
if mode == '-t':
    if len(commands) == 2:
        are_tests_ok = check_directory(global_path, files_amount, 'tests')
        print(are_tests_ok['verdict'])
        if are_tests_ok['check'] == 'OK':
            exit(0)
        else:
            exit(1)
    else:
        is_test_ok = check_file(global_path, int(commands[2]), 'tests')
        print(['Test number {} is OK'.format(commands[2]) if is_test_ok else 'Test number {} not exists'.format(commands[2])][0])
        if is_test_ok:
            exit(0)
        else:
            exit(1)
elif mode == '-a':
    if len(commands) == 2:
        are_answers_ok = check_directory(global_path, files_amount, 'answers')
        print(are_answers_ok['verdict'])
        if are_answers_ok['check'] == 'OK':
            exit(0)
        else:
            exit(1)
    else:
        is_answer_ok = check_file(global_path, int(commands[2]), 'answers')
        print(['Answer number {} is OK'.format(commands[2]) if is_answer_ok else 'Answer number {} not exists'.format(commands[2])][0])
        if is_answer_ok:
            exit(0)
        else:
            exit(1)
elif mode == '-s':
    if len(commands) == 2:
        are_tests_ok = check_directory(global_path, files_amount, 'tests')
        are_answers_ok = check_directory(global_path, files_amount, 'answers')
        is_solution_ok = {'check': 'Solution not checked', 'verdict': 'Solution not checked'}
        if are_tests_ok['check'] == are_answers_ok['check'] == 'OK':
            is_solution_ok = check_solution(global_path, files_amount, time_limit)
        else:
            print('Tests or answers are not right!')
            exit(1)
        print(is_solution_ok['verdict'])
        if is_solution_ok['check'] == 'OK':
            exit(0)
        else:
            exit(1)
    else:
        is_test_ok = check_file(global_path, int(commands[2]), 'tests')
        is_answer_ok = check_file(global_path, int(commands[2]), 'answers')
        is_solution_on_test_ok = 2
        if is_test_ok and is_answer_ok:
            is_solution_on_test_ok = check_solution_on_test(global_path, int(commands[2]), time_limit)
        else:
            print('Tests or answers are not right!')
            exit(1)
        if is_solution_on_test_ok == 0:
            print('Solution on test {} is OK'.format(commands[2]))
        elif is_solution_on_test_ok == 1:
            print('Solution on test {} is OK, but answers are different!'.format(commands[2]))
        else:
            print('Solution on test {} does not work right'.format(commands[2]))
        if is_solution_on_test_ok != 2:
            exit(0)
        else:
            exit(1)
elif mode == '-c':
    if len(commands) == 2:
        print(commands)
        print(global_path)
        are_tests_ok = check_directory(global_path, files_amount, 'tests')
        are_answers_ok = check_directory(global_path, files_amount, 'answers')
        is_solution_ok = {'check': 'Solution not checked', 'verdict': 'Solution not checked'}
        if are_tests_ok['check'] == are_answers_ok['check'] == 'OK':
            is_solution_ok = check_solution(global_path, files_amount, time_limit)
        print(are_tests_ok['verdict'])
        print(are_answers_ok['verdict'])
        print(is_solution_ok['verdict'])
        if are_tests_ok['check'] == are_answers_ok['check'] == is_solution_ok['check'] == 'OK':
            exit(0)
        else:
            exit(1)
    else:
        is_test_ok = check_file(global_path, int(commands[2]), 'tests')
        is_answer_ok = check_file(global_path, int(commands[2]), 'answers')
        is_solution_on_test_ok = 2
        if is_test_ok and is_answer_ok:
            is_solution_on_test_ok = check_solution_on_test(global_path, int(commands[2]), time_limit)
        print(['Test number {} is OK'.format(commands[2]) if is_test_ok else 'Test number {} not exists'.format(commands[2])][0])
        print(['Answer number {} is OK'.format(commands[2]) if is_answer_ok else 'Answer number {} not exists'.format(commands[2])][0])
        if is_solution_on_test_ok == 0:
            print('Solution on test {} is OK'.format(commands[2]))
        elif is_solution_on_test_ok == 1:
            print('Solution on test {} is OK, but answers are different!'.format(commands[2]))
        else:
            print('Solution on test {} does not work right'.format(commands[2]))
        if is_answer_ok and is_test_ok and is_solution_on_test_ok != 2:
            exit(0)
        else:
            exit(1)
else:
    print('Please read readme.txt to learn how to use the script.')
    exit(2)    

