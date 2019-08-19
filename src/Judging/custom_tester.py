#!/usr/bin/python3

from base_tester import BaseTester
import logger
from default_submission import DefaultSubmission

class CustomTester(BaseTester):
    
    def __init__(self):
        BaseTester.__init__(self)
        self.logger = logger.Logger('CustomTester')

    # Overriding inherited method
    async def test(self, submission):
        print('Now we test submission')
        DefaultSubmission(submission).check()


def main():
    tester = CustomTester()

if __name__ == '__main__':
    main()
