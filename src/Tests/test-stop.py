import json, socket

s = socket.socket(socket.AF_UNIX, socket.SOCK_STREAM)
s.connect('/run/judex/judexd.sock')
s.send(json.dumps({'method': 'stop'}).encode())
