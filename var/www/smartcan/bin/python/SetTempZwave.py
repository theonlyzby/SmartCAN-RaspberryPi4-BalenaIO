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
import json
import argparse

device = USBpath()
log="Error"

# Arguments from command prompt
parser = argparse.ArgumentParser(description="zWave - Set Temperature Process")
parser.add_argument("-d", "--debug", help="Debug actions and timings on screen", action="store_true")
parser.add_argument("-n", "--node-ids", nargs="+", type=int, dest="nodeids", help="Provide Zwave Thermostat nodeIDs to get values")
parser.add_argument("-t", "--node-temp", nargs="+", type=float, dest="nodeTemp", help="Provide Zwave Thermostat Temperature set value")

args = parser.parse_args()
if args.debug:           debug=1

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


# Set temps and get values for list of nodes (-n) to generate output json with values
jsondata = {}

i=0
for val in args.nodeids :
  content={}
  if (len(args.nodeTemp)>i):
    if (args.nodeTemp[i]>0):
      # Check current set Temp
      PresentSet = args.nodeTemp[i]
      for val2 in network.nodes[val].get_thermostats() :
        if (network.nodes[val].values[val2].label=="Heat"):
          PresentSet = network.nodes[val].get_thermostat_value(val2)
      # Set Temp ONLY if need to be changed
      if (PresentSet!=args.nodeTemp[i]):
        #print("Node " + str(val) + ", Temp change from " + str(PresentSet) + ", to " + str(args.nodeTemp[i]))
        network.nodes[val].set_thermostat_mode("Heat")
        network.nodes[val].set_thermostat_heating(args.nodeTemp[i])
        content['comfortTemp'] = args.nodeTemp[i]
  # Get temp:
  for valT in network.nodes[val].get_sensors() :
    temp = network.nodes[val].get_sensor_value(valT)
    Unit = network.nodes[val].values[valT].units
    #print("Temp=" + str(temp) + str(Unit))
  # Get battery level:
  batL = network.nodes[val].get_battery_level()
  #print("Battery=" + str(batL))
  #print("nodeID="+str(val)) #args.nodeids[i]
  content['ID']           = args.nodeids[i]
  content['manufacturer'] = "zWave"
  content['temperature']  = round(temp,2)
  content['battery']      = batL
  jsondata['Sensor'+str(val)]   = content
  i=i+1

#Output json with node values (thermostat)
print(json.dumps(jsondata))

# Stop Zwave Network
network.stop()
