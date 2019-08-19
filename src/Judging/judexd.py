#!/usr/bin/python3

import configparser
import os
import subprocess
import random
import socket
import shutil
import json
import asyncio
import queue
import threading
import sys

import logger


class LoadBalancer:

    def __init__(self):
        self._load_configuration()

        self._create_environment()

        self.loop = asyncio.get_event_loop()

        self.logger = logger.Logger('judexd')

        self.testers = set()

        self.testing_queue = queue.Queue(maxsize=int(self.config['judging']['max_submissions_count']))

        self._submission_delivery_task = threading.Thread(target=self._submission_delivery)

        self.run()

    def _create_socket(self):
        path = self.config['judexd']['socket']
        if os.path.exists(path):
            os.remove(path)
        res = socket.socket(socket.AF_UNIX, socket.SOCK_STREAM)
        res.bind(path)
        res.listen()
        res.setblocking(False)
        return res

    def _create_environment(self):
        self.pid = os.getpid()
        if not os.path.exists(self.config['global']['runtime']):
            os.mkdir(self.config['global']['runtime'])
        with open(self.config['judexd']['pid_file'], 'w') as pid_file:
           pid_file.write(str(self.pid))
        if not os.path.exists(self.config['judexd']['testers']):
            os.mkdir(self.config['judexd']['testers'])
        self.socket = self._create_socket()

    def _load_configuration(self):
        self.config = configparser.ConfigParser()
        self.config.read('/etc/judex/judex.conf')

    def run(self):
        self.logger.log('Started')
        try:
            self._submission_delivery_task.start()
            coroutine = self.loop.create_task(self._run())
            self.loop.run_until_complete(coroutine)
        except Exception as e:
            self.logger.log('Internal error at run()')
            self.stop()

    async def _run(self):
        try:
            while True:
                client, address = await self.loop.sock_accept(self.socket)
                self.loop.create_task(self._handle_connected_client(client, address))
        except KeyboardInterrupt:
            # Normal finish
            self.stop()

    def _submission_delivery(self):
        while True:
            print('Waiting for submission')
            submission = self.testing_queue.get()
            query = json.dumps({
                'method': 'test',
                'submission': submission
            })
            if len(self.testers):
                path = random.choice(list(self.testers))
                tester = socket.socket(socket.AF_UNIX, socket.SOCK_STREAM)
                tester.connect(path)
                tester.send(query.encode('utf-8'))
                tester.close()

    async def _handle_connected_client(self, client, address):
        identifier = 'id' + ''.join(random.choices('0123456789', k=5))
        print(f'Client {identifier} connected')
        while True:
            message = (await self.loop.sock_recv(client, 1024)).decode()
            if message:
                await self.loop.create_task(self._handle_message(message, client))
            else:
                break
        client.close()
        print(f'Client {identifier} disconnected')

    async def _handle_message(self, message, client):
        try:
            request = dict(json.loads(message))
        except json.decoder.JSONDecodeError:
            await self.loop.sock_sendall(client, 'Invalid message'.encode())
            return

        if request['method'] == 'add-tester':
            await self.loop.create_task(self.add_tester())
        elif request['method'] == 'event':
            await self.loop.create_task(self._handle_event(request))
        elif request['method'] == 'stop':
            self.stop()
        else:
            print('Unsupported request')

    async def add_tester(self, tester_type='custom_tester.py'):
        executable = os.path.join(self.config['global']['src'], 'Judging/custom_tester.py')
        process = subprocess.Popen(executable)
        socket_path = os.path.join(self.config['global']['runtime'], 'testers', str(process.pid))

    async def _handle_event(self, request):
        try:
            if request['event_name'] == 'tester-created':
                await self._handle_created_tester(request)
            elif request['event_name'] == 'new-submission':
                self.testing_queue.put(request['submission'])
        except KeyError as e:
            print('Bad incoming event format', file=sys.stderr)
        except Exception as e:
            print(e)
            self.stop(f'Internal error\n: {e}')

    async def _handle_created_tester(self, request):
        print('_handle_created_tester')
        path = request['socket_path']
        tester = socket.socket(socket.AF_UNIX, socket.SOCK_STREAM)
        tester.setblocking(False)
        await self.loop.sock_connect(tester, path)
        response = {
            'method': 'response',
            'event_name': 'tester-created',
            'status': 'OK'
        }
        response_message = json.dumps(response).encode()
        await self.loop.sock_sendall(tester, response_message)
        self.testers.add(path)

    def stop(self, error_message=None):
        if error_message:
            self.logger.log(f'Force stop: {error_message}')

        self.logger.log('Stopping testers')
        stop_message = json.dumps({'method': 'stop'})
        for client in self.testers:
            try:
                client.send(stop_message.encode('utf-8'))
            except Exception as e:
                self.logger.log(f'Sending stop message to tester failed:\n{e}')

        try:
            shutil.rmtree(self.config['global']['runtime'])
        except OSError as e:
            self.logger.log(f'Unable to remove runtime data:\n{e}');

        self.logger.log('Stopped')
        if error_message:
            exit(1)
        exit(0)


if __name__ == "__main__":
    lb = LoadBalancer()
