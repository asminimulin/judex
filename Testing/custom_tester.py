from base_tester import BaseTester
import sys

class CustomTester(BaseTester):
    
    def __init__(self, tester_id):
        BaseTester.__init__(self, tester_id)

    def test(self, submission_id, user_id):
        self.connector.send_message('submission #{} scores for 100 points'.format(submission_id))
        self.logger.write('submission #{} scores for 100 points'.format(submission_id))

def main():
    if len(sys.argv) >= 2:
          tester_id = int(sys.argv[1])
          tester = CustomTester(tester_id)
          tester.run()
    else:
        print('Expected int value in argument 1')



if __name__ == '__main__':
    main()
