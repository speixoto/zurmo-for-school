<!DOCTYPE html>
<html>
    <head>
        <style>
            body{
                padding: 50px;
                font-family: sans-serif;
            }
            table{
                border: 1px solid red;
                border-collapse: collapse;
                border-spacing: 1px;
                margin-bottom:10px;
            }
            th{
                text-align: center;
                background: #efefef;
                font-weight: normal;
                color: purple;
                border: 1px solid blue !important;
            }
            th, td{
                border: 1px solid red;
                vertical-align:middle;
                padding:5px 10px;
            }
            .grey td, .grey th{
                background: grey;
            }
            .pink td, .pink th{
                background: pink;
            }
            .gold td, .gold th{
                background: gold;
            }
        </style>
    </head>
    
    <body>
        <h3>Table has 14 columns in total</h3>
        
        <table>
            <thead>
                <tr>
                    <th>colspan 1</th>
                    <th>colspan 2</th>
                    <th colspan="3">colspan 3</th>
                    <th colspan="3">colspan 3</th>
                    <th colspan="3">colspan 3</th>
                    <th colspan="3">colspan 3</th>
                </tr>
                <tr>
                    <th>header 1</th>
                    <th>header 2</th>
                    <th>header 3</th>
                    <th>header 4</th>
                    <th>header 5</th>
                    <th>header 6</th>
                    <th>header 7</th>
                    <th>header 8</th>
                    <th>header 9</th>
                    <th>header 10</th>
                    <th>header 11</th>
                    <th>header 12</th>
                    <th>header 13</th>
                    <th>header 14</th>
                </tr>
            </thead>
            
            <tbody>
                <tr class="grey">
                    <th rowspan="3">header 1<br>ROWSPAN 3</th>
                    <th>header 2</th>
                    <td>cell 3</td>
                    <td>cell 4</td>
                    <td>cell 5</td>
                    <td>cell 6</td>
                    <td>cell 7</td>
                    <td>cell 8</td>
                    <td>cell 9</td>
                    <td>cell 10</td>
                    <td>cell 11</td>
                    <td>cell 12</td>
                    <td>cell 13</td>
                    <td>cell 14</td>
                </tr>
                <tr class="pink">
                    <th>header 1</th>
                    <td>cell 2</td>
                    <td>cell 3</td>
                    <td>cell 4</td>
                    <td>cell 5</td>
                    <td>cell 6</td>
                    <td>cell 7</td>
                    <td>cell 8</td>
                    <td>cell 9</td>
                    <td>cell 10</td>
                    <td>cell 11</td>
                    <td>cell 12</td>
                    <td>cell 13</td>
                </tr>
                <tr class="gold">
                    <th>header 1</th>
                    <td>cell 2</td>
                    <td>cell 3</td>
                    <td>cell 4</td>
                    <td>cell 5</td>
                    <td>cell 6</td>
                    <td>cell 7</td>
                    <td>cell 8</td>
                    <td>cell 9</td>
                    <td>cell 10</td>
                    <td>cell 11</td>
                    <td>cell 12</td>
                    <td>cell 13</td>
                </tr>
               
            </tbody>
            
        </table>
        
      
        
    </body>
</html>