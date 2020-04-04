import os
import json
import subprocess

from . import app
# from .submission import SubmissionContext


class CompilationError(Exception):
    pass


class LanguageHelper:
    LANGUAGES_DIRECTORY = 'Languages'
    LANGUAGE_CONFIG_FILENAME = 'config.json'

    def __init__(self, name: str):
        self.path = os.path.join(app.instance_path, LanguageHelper.LANGUAGES_DIRECTORY, name)
        try:
            config = json.load(open(os.path.join(self.path, LanguageHelper.LANGUAGE_CONFIG_FILENAME), 'r'))
        except FileNotFoundError:
            raise ValueError('Unsupported language')
        self.is_compiled = config['is_compiled']
        self.extension = config['extension']
        if self.is_compiled:
            self.compiler_path = config['compiler_path']
            self.compiler_command_line_arguments = config['command_line_arguments']
        else:
            self.interpreter_path = config['interpreter_path']

    def compile(self, submission_context):
        if not self.is_compiled:
            raise TypeError('Trying to compile not compiled language')
        compiler_output_fp = open(submission_context.get_compiler_output_file(), 'w')
        args = list(self.compiler_command_line_arguments)
        args.append(submission_context.get_executable_file())
        args.append(submission_context.get_source())
        process = subprocess.run([self.compiler_path, *args],
                                 stderr=compiler_output_fp,
                                 stdout=compiler_output_fp)
        return process.returncode == 0
