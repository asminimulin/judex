#!/usr/bin/python3

import sys
import os
import json
import configparser
import shutil

def error(message):
	print('Error. {}'.format(message))
	exit(1)

def compiled(args):
	return '-c' in args or '--compiled' in args

def interpreted(args):
	return '-t' in args or '--interpreted' in args

def get_language_type(args):
	language_type = None
	if compiled(args) and interpreted(args):
		error('Language cannot be interpreted and compiled the same time')
	if compiled(args):
		language_type = 'compiled'
	elif interpreted(args):
		language_type = 'interpreted'
	else:
		t = input('Enter language type (1 - compiled, 2 - interpreted): ')
		if t == '1':
			language_type = 'compiled'
		elif t == '2':
			language_type = 'interpreted'
		else:
			error('Wrong language type')
	return language_type

def get_language_name(args):
	name = input('Enter language name: ')
	if not name:
		error('Empty language name')
	return name

def get_compile_script(args):
	path = input('Enter relative path to compile_script: ')
	d = os.getcwd()
	return f'{d}/{path}'

def language_exists(name):
	return os.path.exists(os.path.join('/opt/judex/languages', name))

def script_valid(script):
	print(f'script = {script}')
	print (os.path.exists(script) + os.path.isfile(script))
	return os.path.exists(script) and os.path.isfile(script)

def configure_language(name, language_type, script=None):
	os.mkdir(os.path.join('/opt/judex/languages', name))
	config = dict()
	config[name] = dict()
	config[name]['name'] = name
	config[name]['type'] = language_type
	if language_type == 'compiled':
		dest_script =  f'/opt/judex/languages/{name}/compile'
		shutil.copyfile(script, dest_script)
		config[name]['compile'] = dest_script
		os.chmod(dest_script, 770)
	path = f'/opt/judex/languages/{name}/config.ini'
	with open(path, 'w') as fp:
		cp = configparser.ConfigParser()
		cp.read_dict(config)
		cp.write(fp)
	path = f'/opt/judex/languages/{name}/config.json'
	with open(path, 'w') as fp:
		json.dump(config, fp, indent=4)

def add_language(args):
	name = get_language_name(args)
	language_type = get_language_type(args)
	script = get_compile_script(args)
	if (language_exists(name)):
		error('Language exists')
	if not script_valid(script):
		error('Invalid script')
	if language_type == 'compiled':
		configure_language(name, language_type, script)
	else:
		configure_language(name, language_type)

if __name__ == "__main__":
	add_language(sys.argv[1:])
