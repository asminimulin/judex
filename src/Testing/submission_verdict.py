#!/usr/bin/python3

import json
import enum

class Verdict():

    class Status(enum.Enum):
        STATUS_CE = 'CE'
        STATUS_WA = 'WA'
        STATUS_SV = 'NLSP'
        STATUS_OK = 'OK'
        STATUS_TLE = 'TL'
        STATUS_RE = 'RE'
        STATUS_MLE = 'ML'
        STATUS_IGN = 'IGN'
        STATUS_PS = 'PS'
        STATUS_LOOSER = 'LOOSER'

    def __init__(self, file_path):
        self.file_path = file_path
        self.tests = []
        self.status = 'RUN'
        self.max_time = 0
        self.max_memory = 0
        self.running_tests_count = 0
        self.time_sum = 0
        self.memory_sum = 0
        self.average_time = 0
        self.average_memory = 0
        self.sum = 0
        self.tests_passed = 0
        self.dump()

    def set_status(self, status_type):
        status = self.Status(status_type)
        self.status = status.value
        self.dump()

    def add_test(self, status_type, time, memory, count=1):
        assert(count > 0)
        status = self.Status(status_type)
        self.max_time = max(self.max_time, time)
        self.max_memory = max(self.max_memory, memory)
        if status is self.Status.STATUS_OK:
            self.tests_passed += count
        if status is not self.Status.STATUS_IGN:
            self.time_sum = round(count * time + self.time_sum, 3)
            self.memory_sum += count * memory
            self.running_tests_count += count
            self.average_time = round(self.time_sum / self.running_tests_count, 3)
            self.average_memory = self.memory_sum // self.running_tests_count
        for _ in range(count):
            self.tests.append("{};{};{}".format(status.value, time, memory))
        self.dump()

    def score(self, value):
        self.sum += value
        self.dump()

    def dump(self):
        with open(self.file_path, 'w') as f:
            json.dump(self.__dict__, f)

def main():
    verdict = Verdict('result.json')
    verdict.set_status(Verdict.Status.STATUS_CE)
    verdict.dump()

if __name__ == '__main__':
    main()
