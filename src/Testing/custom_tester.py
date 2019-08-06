#!/usr/bin/python3

from base_tester import BaseTester
import logger
from default_submission import DefaultSubmission

DEBUG = True


class CustomTester(BaseTester):
    
    def __init__(self):
        BaseTester.__init__(self)
        self.logger = logger.Logger('CustomTester')

    # Overriding inherited method
    def test(self, submission):
        DefaultSubmission(submission).check()


def main():
    tester = CustomTester()
    tester.run()


if __name__ == '__main__':
    main()
