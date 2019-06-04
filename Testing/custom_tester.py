from base_tester import BaseTester
import sys
from submission_runner import SubmissionRunner
import logger

class CustomTester(BaseTester):
    
    def __init__(self, tester_id):
        BaseTester.__init__(self, tester_id)
        self.runner = SubmissionRunner()
        self.logger = logger.Logger('CustomTester')

    def test(self, submission_id, user_id, language):
        self.runner.check_submission(submission_id, user_id, language)
        self.connector.send_message('submission #{} scores for 100 points'.format(submission_id))

def main():
    if len(sys.argv) >= 2:
          tester_id = int(sys.argv[1])
          tester = CustomTester(tester_id)
          tester.run()
    else:
        print('Expected int value in argument 1')



if __name__ == '__main__':
    main()
