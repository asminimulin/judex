import pymysql
import configparser
from logger import Logger

class RatingCounter:

    def __init__(self):
        self.connection = None
        self.config = configparser.ConfigParser()
        self.config.read('/etc/judex/judex.conf')
        self.logger = Logger('RatingController')
        self._connect()

    def _connect(self):
        self.connection = pymysql.connect(host=self.config['database']['host'],
                                          user=self.config['database']['user'],
                                          password=self.config['database']['password'],
                                          db=self.config['database']['dbname'],
                                          charset='utf8',
                                          cursorclass=pymysql.cursors.DictCursor
                )

    def process_result(self, result):
        score = result['sum']
        user_id = result['user_id']
        problem_id = result['problem_id']
        prev_score = self._check_result(user_id, problem_id)
        diff = score // 10 - prev_score // 10
        if diff <= 0:
            return
        self.update_user_result(user_id, problem_id, score)
        old_cost = self.get_problem_cost(problem_id)
        new_cost = max(100, old_cost - diff)
        self.set_problem_cost(problem_id, new_cost)
        self._update_rating(problem_id, old_cost, new_cost, user_id, prev_score)

    def _recount_user_rating(self, user_id, old_score, new_score, old_cost, new_cost):
        rating = self.get_user_rating(user_id)
        rating -= int(old_cost * (old_score / 100))
        rating += int(new_cost * (new_score / 100))
        print(rating)
        return rating

    def _update_rating(self, problem_id, old_cost, new_cost, user_id, user_old_score):
        query = 'SELECT * FROM user_result WHERE problem_id={}'.format(problem_id)
        users = self._send_query(query, 'ALL')
        for user in users:
            print(user)
            old_score = user['SCORE']
            if user['user_id'] == user_id:
                old_score = user_old_score
            self.set_user_rating(user['user_id'],
                    self._recount_user_rating(user['user_id'],
                        old_score, user['SCORE'], old_cost, new_cost))
            print('PIZDA')

    def update_user_result(self, user_id, problem_id, score):
        query_insert = 'INSERT INTO user_result VALUES({}, {}, {}, {})'.format(user_id, problem_id, int(score == 100), score)
        query_delete = 'DELETE FROM user_result WHERE user_id={} AND problem_id={}'.format(user_id, problem_id)
        self._send_query(query_delete, 'NO')
        self._send_query(query_insert, 'NO')
            
    def set_user_rating(self, user_id, new_rating):
        print(user_id, new_rating, 'user id new rating')
        query = 'UPDATE users SET rating={} WHERE id={}'.format(new_rating, user_id)
        self._send_query(query, 'NO')

    def get_user_rating(self, user_id):
        query = 'SELECT rating FROM users WHERE id={}'.format(user_id)
        return self._send_query(query, 'ONE')['rating']

    def _send_query(self, query, need_result='NO'):
        try:
            with self.connection.cursor() as cursor:
                cursor.execute(query)
                if need_result == 'ONE':
                    return cursor.fetchone()
                elif need_result == 'ALL':
                    return [elem for elem in cursor]
            self.connection.commit()
        except Exception:
            self.logger.log('Connection with database was lost')
            return None

    def _check_result(self, user_id, problem_id):
        query = 'SELECT score FROM user_result WHERE user_id={} AND problem_id={}'.format(user_id, problem_id)
        response = self._send_query(query, 'ONE')
        if not response:
            return 0
        else:
            return response['score']
        
    def get_problem_cost(self, problem_id):
        query = 'SELECT cost FROM problems WHERE id={}'.format(str(problem_id))
        return self._send_query(query, 'ONE')['cost']

    def set_problem_cost(self, problem_id, new_cost):
        query = 'UPDATE problems SET cost={} WHERE id={}'.format(str(new_cost), str(problem_id))
        self._send_query(query)


c = RatingCounter()
while 1:
    print('<user_id> <problem_id> <sum>')
    query = [int(x) for x in input().split()]
    result = {'user_id': query[0], 'problem_id': query[1], 'sum': query[2]}
    c.process_result(result)
    print('Query OK, new user rating is', c.get_user_rating(result['user_id']), 'and new problem cost is', c.get_problem_cost(result['problem_id']))
#   
