# python3 ./test.py -n 4 5

import json
import argparse

# Arguments from command prompt
parser = argparse.ArgumentParser(description="Hydrokube controller Software:", epilog="Conuslt the user guide more all information.")
parser.add_argument("-d", "--debug", help="Debug actions and timings on screen", action="store_true")
parser.add_argument("-n", "--node-ids", nargs="+", type=int, dest="nodeids", help="Provide Zwave Thermostat nodeIDs to set and get values")
parser.add_argument("-t", "--node-temp", nargs="+", type=float, dest="nodeTemp", help="Provide Zwave Thermostat Temperature set value")

args = parser.parse_args()
if args.debug:           debug=1
#if args.node-ids:        node-ids = args.node-ids


#print("List of items: {}".format(args.nodeids[0]))

#print(format(args.nodeTemp))

jsondata = {}
#content={}
i=0 #args.nodeids[i] in globals()
for val in args.nodeids:
  content={}
  temp=0
  if (len(args.nodeTemp)>i): temp = args.nodeTemp[i]
  print("nodeID="+str(val)) #args.nodeids[i]
  content['ID']           = args.nodeids[i]
  content['manufacturer'] = "zWave"
  content['temperature']  = temp
  content['battery']      = 90
  jsondata['"Sensor'+str(val)+'"']   = content
  i=i+1

print(json.dumps(jsondata))






















# python3 ./test.py -n 4 5

import json
import argparse

# Arguments from command prompt
parser = argparse.ArgumentParser(description="Hydrokube controller Software:", epilog="Conuslt the user guide more all information.")
parser.add_argument("-d", "--debug", help="Debug actions and timings on screen", action="store_true")
parser.add_argument("-n", "--node-ids", nargs="+", type=int, dest="nodeids", help="Provide Zwave Thermostat nodeIDs to set and get values")
parser.add_argument("-t", "--node-temp", nargs="+", type=int, dest="nodeTemp", help="Provide Zwave Thermostat Temperature set value")

args = parser.parse_args()
if args.debug:           debug=1
#if args.node-ids:        node-ids = args.node-ids


#print("List of items: {}".format(args.nodeids[0]))

#,args.nodeTemp[i]

jsondata = {}
content={}
i=0 #args.nodeids[i] in globals()
for val in args.nodeids  :
  print("nodeID="+str(val)) #args.nodeids[i]
  content['ID']           = args.nodeids[i]
  content['manufacturer'] = "zWave"
  content['temperature']  = round(27.55,2)
  content['battery']      = 90
  jsondata['Sensor'+str(val)]   = content
  i=i+1

print(json.dumps(jsondata))









input=({
	"data": {
		"Sensor00": {
			"ID": "004",
			"temperature": 22.4,
			"battery": 95
		},
		"Sensor01": {
			"ID": "005",
			"temperature": 22.4,
			"battery": 95
		}
	}

})

#json_dump = json.dumps(input)
#print(json_dump);






#content['ID']           = 4
#content['manufacturer'] = "zWave"
#content['temperature']  = 27.0
#content['battery']      = 90
#jsondata['Sensor000']   = content


#content['ID']           = 5
#content['manufacturer'] = "zWave"
#content['temperature']  = 25.5
#content['battery']      = 95
#jsondata['Sensor001']   = content

#print(json.dumps(jsondata))













#for node in network.nodes:
#    print("{} - ProductName : {}".format(network.nodes[node].node_id,network.nodes[node].product_name))

#network.nodes[4].set_thermostat_mode('Heat')
#network.nodes[4].set_thermostat_heating(22.5)

#temp_set = 0
#for val in network.nodes[4].get_sensors() :
#  temp     = network.nodes[4].get_sensor_value(val)
#  temp_u   = network.nodes[4].values[val].units
#for val in network.nodes[4].get_thermostats() :
#  if(network.nodes[4].values[val].label=='Heat'):
#    temp_set = network.nodes[4].get_thermostat_value(val)
#for val in network.nodes[4].get_battery_levels() :
#  bat_lev  = network.nodes[4].get_battery_level(val)

#print("Temp="+str(temp)+", temp Unit="+temp_u+", temp_set="+str(temp_set)+", bat_level="+str(bat_lev))
