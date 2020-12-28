#!/usr/bin/env python3

import logging
import sys, os
import resource
#logging.getLogger('openzwave').addHandler(logging.NullHandler())
#logging.basicConfig(level=logging.DEBUG)
logging.basicConfig(level=logging.ERROR)

logger = logging.getLogger('openzwave')
import openzwave
from openzwave.node import ZWaveNode
from openzwave.value import ZWaveValue
from openzwave.scene import ZWaveScene
from openzwave.controller import ZWaveController
from openzwave.network import ZWaveNetwork
from openzwave.option import ZWaveOption
import time
from zWaveConfig import USBpath, ConfigPath

device = USBpath()

log="Error"

for arg in sys.argv:
    if arg.startswith("--device"):
        temp,device = arg.split("=")
    elif arg.startswith("--log"):
        temp,log = arg.split("=")
    elif arg.startswith("--help"):
        print("help : ")
        print("  --device=/dev/yourdevice ")
        print("  --log=Info|Debug")

#Define some manager options
options = ZWaveOption(device, \
  config_path=ConfigPath(), \
  user_path=".", cmd_line="")
options.set_log_file("OZW_Log.log")
options.set_append_log_file(False)
options.set_console_output(False)
options.set_save_log_level(log)
#options.set_save_log_level('Info')
options.set_logging(False)
options.lock()

#Create a network object
network = ZWaveNetwork(options, log=None)

time_started = 0

for i in range(0,300):
    if network.state>=network.STATE_AWAKED:
        break
    else:
        time_started += 1
        time.sleep(1.0)

for i in range(0,300):
    if network.state>=network.STATE_READY:
        break
    else:
        time_started += 1
        #sys.stdout.write(network.state_str)
        #sys.stdout.write("(")
        #sys.stdout.write(str(network.nodes_count))
        #sys.stdout.write(")")
        #sys.stdout.write(".")
        sys.stdout.flush()
        time.sleep(1.0)


for node in network.nodes:
   print("{} - Manufacturer name / id : {} / {}".format(network.nodes[node].node_id,network.nodes[node].manufacturer_name, network.nodes[node].manufacturer_id))
   print("{} - Product name / id / type : {} / {} / {}".format(network.nodes[node].node_id,network.nodes[node].product_name, network.nodes[node].product_id, network.nodes[node].product_type))
 



network.stop()
