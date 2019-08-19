import json, socket
import time
s = socket.socket(socket.AF_UNIX, socket.SOCK_STREAM)
s.connect('/run/judex/judexd.sock')
msg = json.dumps({'method': 'add-tester'}).encode()
s.send(msg)
s.close()
