#!/usr/bin/python3

import configparser
import json
import socket
import asyncio
import logger
import os


class BaseTester:

    def _create_socket(self):
        s = socket.socket(socket.AF_UNIX, socket.SOCK_STREAM)
        self.socket_path = '/run/judex/testers/{}/tester.sock'.format(os.getpid())
        s.bind(self.socket_path)
        s.listen()
        return s

    def _notify_created(self):
        path = self.config['judexd']['socket']
        client = socket.socket(socket.AF_UNIX, socket.SOCK_STREAM)
        client.connect(path)
        query = {
            'method': 'event',
            'event_name': 'tester-created',
            'socket_path': self.socket_path
        }
        query_message = json.dumps(query).encode()
        client.send(query_message)
        client.close()

        client, address = self.socket.accept()
        response_message = client.recv(1024).decode()
        try:
            response = json.loads(response_message)
        except json.JSONDecodeError:
            exit(1)

        try:
            if response['method'] != 'response':
                exit(1)
            if response['event_name'] != 'tester-created':
                exit(1)
            if response['status'] != 'OK':
                exit(1)
        except KeyError as e:
            exit(1)
        print('ok tester is ready')

    def _init_environment(self):
        directory = os.path.join(self.config['judexd']['testers'], str(self.id))
        if not os.path.exists(directory):
            os.mkdir(directory)

    def __init__(self):
        self.id = os.getpid()
        self.config = configparser.ConfigParser()
        self.config.read('/etc/judex/judex.conf')
        self._init_environment()
        self.logger = logger.Logger('BaseTester')
        self.socket = self._create_socket()
        self._notify_created()
        self.loop = asyncio.get_event_loop()
        self.run()

    def run(self):
        self.logger.log('Started')
        coroutine = self.loop.create_task(self._run())
        self.loop.run_until_complete(coroutine)

    async def _handle_connected_client(self, client):
        while True:
            message = (await self.loop.sock_recv(client, 1024)).decode()
            if not message:
                break
            self.loop.create_task(self._handle_message(message))

    async def _run(self):
        while True:
            client, address = await self.loop.sock_accept(self.socket)
            await self.loop.create_task(self._handle_connected_client(client))

    async def _handle_message(self, message):
        try:
            request = dict(json.loads(message))
        except json.JSONDecodeError:
            return
        if request['method'] == 'test':
            self.loop.create_task(self.test(request['submission']))
        elif request['method'] == 'stop':
            self.logger.log('stopped')
            exit(0)
        else:
            self.logger.log('Unhandled message: {}'.format(message))

    async def test(self, submission):
        raise NotImplementedError('You must implement this method to test submission')
