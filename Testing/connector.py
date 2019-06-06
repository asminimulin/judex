#!/usr/bin/python3

import os
import stat

class ConnectorInterface:

    def __init__(self, path_in, path_out):
        '''
        args:
            path_in - path to file, wich current process uses as IPC input
            path_out - path to file, wich current process uses as IPC output
        '''
        raise NotImplementedError('__init__')

    def has_message(self):
        '''
        return value:
            if process has incoming message -> return True
            else -> return False
        '''
        raise NotImplementedError('has_message')

    def get_message(self):
        '''
        return value:
            return str object if process has incoming messages
            else return None
        '''
        raise NotImplementedError('get_message')

    def send_message(self, message):
        '''
        args:
            message - str object wich is sent to process in other end of pipe
                if message is None -> raise Exception
        '''
        raise NotImplementedError('send_message')

class BaseConnector(ConnectorInterface):
    
    def has_message(self):
        if not self.line:
            self.line = self.input.readline()
        if not self.line:
            return False
        return True


    def get_message(self):
        if not self.has_message():
            return None
        res = self.line
        self.line = None
        return res

    def send_message(self, message):
        if not message:
            raise Exception('ERROR: Message is empty')
        self.output.write(message + '\n')
        self.output.flush()
    
class ChildConnector(BaseConnector):
    def __init__(self, path_in, path_out): 
        self.line = None
        if not os.path.exists(path_in):
            os.mkfifo(path_in)
        if not os.path.exists(path_out):
            os.mkfifo(path_out)

        if not stat.S_ISFIFO(os.stat(path_in).st_mode):
            raise Exception('Connector init error path_in={} is not FIFO.'.format(path_in))
        if not stat.S_ISFIFO(os.stat(path_out).st_mode):
            raise Exception('Connector init error path_out={} is not FIFO.'.format(path_out))

        self.input = open(path_in, 'r')
        self.output = open(path_out, 'w')

class ParentConnector(BaseConnector):
    def __init__(self, path_in, path_out):
        self.line = None
        if not os.path.exists(path_in):
            os.mkfifo(path_in)
        if not os.path.exists(path_out):
            os.mkfifo(path_out)
        #if not os.path.exists(path_in):
        #    raise Exception('File <' + path_in + '> used as input of parent process not exists')
        #if not os.path.exists(path_out):
        #    raise Exception('File <' + path_out + '> used as output of parent process not exists')

        if not stat.S_ISFIFO(os.stat(path_in).st_mode):
            raise Exception('Connector init error path_in={} is not FIFO.'.format(path_in))
        if not stat.S_ISFIFO(os.stat(path_out).st_mode):
            raise Exception('Connector init error path_out={} is not FIFO.'.format(path_out))

        self.output = open(path_out, 'w')
        self.input = open(path_in, 'r')

