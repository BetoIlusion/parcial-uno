<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UML Class Diagram</title>
    <!-- Include GoJS library -->
    <script src="https://unpkg.com/gojs@2.3.18/release/go.js"></script>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f0f0f0;
        }

        #container {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
            max-width: 800px;
        }

        #leftPanel {
            width: 100%;
            height: 80vh;
            border: 1px solid black;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            align-items: center;
            background-color: white;
        }

        #myDiagramDiv {
            width: 100%;
            height: calc(100% - 50px);
            /* Space for button */
            border: 1px solid black;
            flex-grow: 1;
        }

        #buttonPanel {
            padding: 10px;
            text-align: center;
            width: 100%;
        }
    </style>
</head>

<body>
    <div id="container">
        <div id="leftPanel">
            <div id="buttonPanel">
                <button onclick="addNewClass()">Add New Class</button>
            </div>
            <div id="myDiagramDiv"></div>
        </div>
    </div>

    <script>
        // Initialize GoJS diagram
        function init() {
            // Access the diagram model passed from Laravel
            var jsonInicial = @json($jsonInicial);

            // Initialize the diagram
            var myDiagram = new go.Diagram('myDiagramDiv', {
                'undoManager.isEnabled': true,
                layout: new go.TreeLayout({
                    angle: 90,
                    path: go.TreePath.Source,
                    setsPortSpot: false,
                    setsChildPortSpot: false,
                    arrangement: go.TreeArrangement.Horizontal
                })
            });

            // Convert visibility to symbols
            function convertVisibility(v) {
                switch (v) {
                    case 'public':
                        return '+';
                    case 'private':
                        return '-';
                    case 'protected':
                        return '#';
                    case 'package':
                        return '~';
                    default:
                        return v;
                }
            }

            // Property template
            var propertyTemplate = new go.Panel('Horizontal')
                .add(
                    new go.TextBlock({
                        isMultiline: false,
                        editable: false,
                        width: 12
                    })
                    .bind('text', 'visibility', convertVisibility),
                    new go.TextBlock({
                        isMultiline: false,
                        editable: true
                    })
                    .bindTwoWay('text', 'name')
                    .bind('isUnderline', 'scope', s => s[0] === 'c'),
                    new go.TextBlock('')
                    .bind('text', 'type', t => t ? ': ' : ''),
                    new go.TextBlock({
                        isMultiline: false,
                        editable: true
                    })
                    .bindTwoWay('text', 'type'),
                    new go.TextBlock({
                        isMultiline: false,
                        editable: false
                    })
                    .bind('text', 'default', s => s ? ' = ' + s : '')
                );

            // Method template
            var methodTemplate = new go.Panel('Horizontal')
                .add(
                    new go.TextBlock({
                        isMultiline: false,
                        editable: false,
                        width: 12
                    })
                    .bind('text', 'visibility', convertVisibility),
                    new go.TextBlock({
                        isMultiline: false,
                        editable: true
                    })
                    .bindTwoWay('text', 'name')
                    .bind('isUnderline', 'scope', s => s[0] === 'c'),
                    new go.TextBlock('()')
                    .bind('text', 'parameters', parr => {
                        var s = '(';
                        for (var i = 0; i < parr.length; i++) {
                            var param = parr[i];
                            if (i > 0) s += ', ';
                            s += param.name + ': ' + param.type;
                        }
                        return s + ')';
                    }),
                    new go.TextBlock('')
                    .bind('text', 'type', t => t ? ': ' : ''),
                    new go.TextBlock({
                        isMultiline: false,
                        editable: true
                    })
                    .bindTwoWay('text', 'type')
                );

            // Node template
            myDiagram.nodeTemplate = new go.Node('Auto', {
                    locationSpot: go.Spot.Center,
                    fromSpot: go.Spot.AllSides,
                    toSpot: go.Spot.AllSides
                })
                .add(
                    new go.Shape({
                        fill: 'lightyellow'
                    }),
                    new go.Panel('Table', {
                        defaultRowSeparatorStroke: 'black'
                    })
                    .add(
                        new go.TextBlock({
                            row: 0,
                            columnSpan: 2,
                            margin: 3,
                            alignment: go.Spot.Center,
                            font: 'bold 12pt sans-serif',
                            isMultiline: false,
                            editable: true
                        })
                        .bindTwoWay('text', 'name'),
                        new go.TextBlock('Properties', {
                            row: 1,
                            font: 'italic 10pt sans-serif'
                        })
                        .bindObject('visible', 'visible', v => !v, undefined, 'PROPERTIES'),
                        new go.Panel('Vertical', {
                            name: 'PROPERTIES',
                            row: 1,
                            margin: 3,
                            stretch: go.Stretch.Horizontal,
                            defaultAlignment: go.Spot.Left,
                            background: 'lightyellow',
                            itemTemplate: propertyTemplate
                        })
                        .bind('itemArray', 'properties'),
                        go.GraphObject.build("PanelExpanderButton", {
                            row: 1,
                            column: 1,
                            alignment: go.Spot.TopRight,
                            visible: false
                        }, "PROPERTIES")
                        .bind('visible', 'properties', arr => arr.length > 0),
                        new go.TextBlock('Methods', {
                            row: 2,
                            font: 'italic 10pt sans-serif'
                        })
                        .bindObject('visible', 'visible', v => !v, undefined, 'METHODS'),
                        new go.Panel('Vertical', {
                            name: 'METHODS',
                            row: 2,
                            margin: 3,
                            stretch: go.Stretch.Horizontal,
                            defaultAlignment: go.Spot.Left,
                            background: 'lightyellow',
                            itemTemplate: methodTemplate
                        })
                        .bind('itemArray', 'methods'),
                        go.GraphObject.build("PanelExpanderButton", {
                            row: 2,
                            column: 1,
                            alignment: go.Spot.TopRight,
                            visible: false
                        }, "METHODS")
                        .bind('visible', 'methods', arr => arr.length > 0)
                    )
                );


            // Link style
            function linkStyle() {
                return {
                    isTreeLink: false,
                    fromEndSegmentLength: 0,
                    toEndSegmentLength: 0
                };
            }

            // Link templates
            myDiagram.linkTemplate = new go.Link({
                    ...linkStyle(),
                    isTreeLink: true
                })
                .add(
                    new go.Shape(),
                    new go.Shape({
                        toArrow: 'Triangle',
                        fill: 'white'
                    })
                );

            myDiagram.linkTemplateMap.add('Association',
                new go.Link(linkStyle())
                .add(new go.Shape())
            );

            myDiagram.linkTemplateMap.add('Realization',
                new go.Link(linkStyle())
                .add(
                    new go.Shape({
                        strokeDashArray: [3, 2]
                    }),
                    new go.Shape({
                        toArrow: 'Triangle',
                        fill: 'white'
                    })
                ));

            myDiagram.linkTemplateMap.add('Dependency',
                new go.Link(linkStyle())
                .add(
                    new go.Shape({
                        strokeDashArray: [3, 2]
                    }),
                    new go.Shape({
                        toArrow: 'OpenTriangle'
                    })
                ));

            myDiagram.linkTemplateMap.add('Composition',
                new go.Link(linkStyle())
                .add(
                    new go.Shape(),
                    new go.Shape({
                        fromArrow: 'StretchedDiamond',
                        scale: 1.3
                    }),
                    new go.Shape({
                        toArrow: 'OpenTriangle'
                    })
                ));

            myDiagram.linkTemplateMap.add('Aggregation',
                new go.Link(linkStyle())
                .add(
                    new go.Shape(),
                    new go.Shape({
                        fromArrow: 'StretchedDiamond',
                        fill: 'white',
                        scale: 1.3
                    }),
                    new go.Shape({
                        toArrow: 'OpenTriangle'
                    })
                ));

            // Set the model from Laravel
            myDiagram.model = new go.GraphLinksModel({
                copiesArrays: true,
                copiesArrayObjects: true,
                linkCategoryProperty: 'relationship',
                ...jsonInicial
            });
            // Debajo de: myDiagram.model = new go.GraphLinksModel({...});

            // Definir el menú contextual
            var contextMenu = new go.Adornment("Vertical")
                .add(
                    new go.Panel("Auto", {
                        margin: 2
                    })
                    .add(
                        new go.Shape({
                            fill: "white",
                            stroke: "gray",
                            width: 120,
                            height: 30
                        }),
                        new go.TextBlock("Añadir Atributo", {
                            margin: 5
                        })
                    )
                    .set({
                        click: function(e, obj) {
                            var node = e.diagram.selection.first();
                            if (node) {
                                e.diagram.startTransaction("addAttribute");
                                var newAttr = {
                                    name: "newAttr",
                                    type: "String",
                                    visibility: "public"
                                };
                                var data = node.data;
                                e.diagram.model.addArrayItem(data.properties, newAttr);
                                e.diagram.commitTransaction("addAttribute");
                            }
                        }
                    }),
                    new go.Panel("Auto", {
                        margin: 2
                    })
                    .add(
                        new go.Shape({
                            fill: "white",
                            stroke: "gray",
                            width: 120,
                            height: 30
                        }),
                        new go.TextBlock("Añadir Operación", {
                            margin: 5
                        })
                    )
                    .set({
                        click: function(e, obj) {
                            var node = e.diagram.selection.first();
                            if (node) {
                                e.diagram.startTransaction("addOperation");
                                var newOp = {
                                    name: "newOperation",
                                    parameters: [],
                                    visibility: "public",
                                    type: ""
                                };
                                var data = node.data;
                                e.diagram.model.addArrayItem(data.methods, newOp);
                                e.diagram.commitTransaction("addOperation");
                            }
                        }
                    })
                );

            // Asignar el menú contextual al nodeTemplate
            myDiagram.nodeTemplate.contextMenu = contextMenu;

            // Function to add a new class
            window.addNewClass = function() {
                myDiagram.startTransaction('addClass');
                var newClass = {
                    key: myDiagram.model.nodeDataArray.length + 1,
                    name: 'Class' + (myDiagram.model.nodeDataArray.length + 1),
                    properties: [],
                    methods: []
                };
                myDiagram.model.addNodeData(newClass);
                myDiagram.commitTransaction('addClass');
            };
        }

        // Initialize when DOM is loaded
        window.addEventListener('DOMContentLoaded', init);
    </script>
</body>

</html>
