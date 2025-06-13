<?php
  ## Basic details to outline the pages.
  ## Essentially static HTML
  $tuition = [
       {
          "2024":{
             "grad-other":{
                "name":"General Education",
                "type":"credit",
                "heartland":"768.60",
                "resident":"512.40",
                "nonresident":"1286.25",
                "hours":"${group.getChild('hours').textValue}"
             },
             "dental-surgery":{
                "name":"Dental Surgery",
                "type":"flat",
                "heartland":"${group.getChild('heartland').textValue}",
                "resident":"21675.00",
                "nonresident":"42898.00",
                "hours":"16"
             },
             "dentistry-advanced":{
                "name":"Advanced Education in General Dentistry",
                "type":"flat",
                "heartland":"${group.getChild('heartland').textValue}",
                "resident":"1427.91",
                "nonresident":"2828.66",
                "hours":"14"
             },
             "dentistry-standing":{
                "name":"Dentistry Advanced Standing Program",
                "type":"flat",
                "heartland":"${group.getChild('heartland').textValue}",
                "resident":"41484.00",
                "nonresident":"${group.getChild('nonresident').textValue}",
                "hours":"${group.getChild('hours').textValue}"
             },
             "law-jd":{
                "name":"Juris Doctorate",
                "type":"credit",
                "heartland":"${group.getChild('heartland').textValue}",
                "resident":"752.40",
                "nonresident":"961.40",
                "hours":"${group.getChild('hours').textValue}"
             },
             "law-llm":{
                "name":"LL.M.",
                "type":"credit",
                "heartland":"${group.getChild('heartland').textValue}",
                "resident":"913.30",
                "nonresident":"1872.64",
                "hours":"${group.getChild('hours').textValue}"
             },
             "law-mls":{
                "name":"MLS",
                "type":"credit",
                "heartland":"${group.getChild('heartland').textValue}",
                "resident":"660.00",
                "nonresident":"${group.getChild('nonresident').textValue}",
                "hours":"${group.getChild('hours').textValue}"
             },
             "med-early":{
                "name":"Medicine, Year 1-2",
                "type":"flat",
                "heartland":"20312.25",
                "resident":"13643.70",
                "nonresident":"26980.80",
                "hours":"16"
             },
             "med-late":{
                "name":"Medicine, Year 3-6",
                "type":"flat",
                "heartland":"23889.60",
                "resident":"16039.80",
                "nonresident":"31735.20",
                "hours":"18"
             },
             "med-anesthesia":{
                "name":"Anesthesia",
                "type":"credit",
                "heartland":"${group.getChild('heartland').textValue}",
                "resident":"15296.40",
                "nonresident":"18187.05",
                "hours":"${group.getChild('hours').textValue}"
             },
             "med-pa":{
                "name":"Physician Assistant",
                "type":"flat",
                "heartland":"${group.getChild('heartland').textValue}",
                "resident":"13795.95",
                "nonresident":"16474.50",
                "hours":"18"
             },
             "nursing-hs":{
                "name":"Nursing and Health Studies",
                "type":"credit",
                "heartland":"${group.getChild('heartland').textValue}",
                "resident":"742.35",
                "nonresident":"${group.getChild('nonresident').textValue}",
                "hours":"18"
             },
             "pharmacy":{
                "name":"Pharmacy",
                "type":"flat",
                "heartland":"${group.getChild('heartland').textValue}",
                "resident":"15222.78",
                "nonresident":"15222.78",
                "hours":"15"
             }
          }
       }
    ]