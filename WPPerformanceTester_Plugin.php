<?php
require_once( 'WPPerformanceTester_LifeCycle.php' );
require_once( 'benchmark.php' );

class WPPerformanceTester_Plugin extends WPPerformanceTester_LifeCycle {

    /**
    * Override settingsPage()
    */
    public function settingsPage() {

        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        $performTest = false;
        if ( !empty( $_POST['performTest'] ) && ( $_POST['performTest'] == true ) ) {
            $performTest=true;
        }
        ?>
        <div class="wrap">
            <h2>WPPerformanceTester</h2>
            <p>WPPerformanceTester performs a series of tests to see how well your server performs. The first set test the raw server performance. The second is WordPress specific. Your results will be displayed and you can see how your results stack up against others.</p>

            <form method="post" action="<?php echo esc_url( admin_url('tools.php?page=WPPerformanceTester_PluginSettings') ); ?>">
                <input type="hidden" name="performTest" value="true">
                <input type="submit" value="Begin Performance Test" onclick="this.value='This may take a minute...'">
            </form>

            <?php
            if ( $performTest ) {
                //do test
                global $wpdb;
                $arr_cfg = array();

                // We need special handling for hyperdb
                if ( is_a( $wpdb, 'hyperdb' ) && ! empty( $wpdb->hyper_servers ) ) {
                  // Grab a `write` server for the `global` dataset and fallback to `read`.
                  // We're not really paying attention to priority or have much in the way of error checking. Use at your own risk :)
                  $db_server = false;
                  if ( ! empty( $wpdb->hyper_servers['global']['write'] ) ) {
                    foreach ( $wpdb->hyper_servers['global']['write'] as $group => $dbs ) {
                      $db_server = current( $dbs );
                      break;
                    }
                  } elseif ( ! empty( $wpdb->hyper_servers['global']['read'] ) ) {
                    foreach ( $wpdb->hyper_servers['global']['read'] as $group => $dbs ) {
                      $db_server = current( $dbs );
                      break;
                    }
                  }

                  if ( $db_server ) {
                    $arr_cfg['db.host'] = $db_server['host'];
                    $arr_cfg['db.user'] = $db_server['user'];
                    $arr_cfg['db.pw'] = $db_server['password'];
                    $arr_cfg['db.name'] = $db_server['name'];
                  }
                } else {
                  // Vanilla WordPress install with standard `wpdb`
                  $arr_cfg['db.host'] = DB_HOST;
                  $arr_cfg['db.user'] = DB_USER;
                  $arr_cfg['db.pw'] = DB_PASSWORD;
                  $arr_cfg['db.name'] = DB_NAME; 
                }
                
                $arr_benchmark = test_benchmark($arr_cfg);
                $arr_wordpress = test_wordpress();
              

                //charting from results goes here
                ?>
                <h2>Performance Test Results (in seconds)</h2>
                <div id="chartDiv">
                    <div id="legendDiv"></div>
                    <canvas id="myChart" height="400" width="600"></canvas>
                    <p style="text-align: center; font-style: italic;">Test Type</p>
                </div>
                <p>* Lower (faster) time is better. Please submit your results to improve our industry average data :)</p>
                <script>
                jQuery(document).ready(function(){
                    jQuery.getJSON( "https://wphreviews.com/api/wpperformancetester.php", function( industryData ) { 
                        var ctx = document.getElementById("myChart").getContext("2d");
                        
                        var data = {
                            labels: ["Math (CPU)", "String (CPU)", "Loops (CPU)", "Conditionals (CPU)", "MySql (Database)", "Server Total", "WordPress Performance"],
                            datasets: [
                                {
                                    label: "Your Results",
                                    fillColor: "rgba(151,187,205,0.5)",
                                    strokeColor: "rgba(151,187,205,0.8)",
                                    highlightFill: "rgba(151,187,205,0.75)",
                                    highlightStroke: "rgba(151,187,205,1)",                                    
                                    data: [<?php echo $arr_benchmark['benchmark']['math']; ?>, <?php echo $arr_benchmark['benchmark']['string']; ?>, <?php echo $arr_benchmark['benchmark']['loops']; ?>, <?php echo $arr_benchmark['benchmark']['ifelse']; ?>, <?php echo $arr_benchmark['benchmark']['mysql_query_benchmark']; ?>, <?php echo $arr_benchmark['total']; ?>, <?php echo $arr_wordpress['time']; ?>]
                                },
                                {
                                    label: "Industry Average",
                                    fillColor: "rgba(130,130,130,0.5)",
                                    strokeColor: "rgba(130,130,130,0.8)",
                                    highlightFill: "rgba(130,130,130,0.75)",
                                    highlightStroke: "rgba(130,130,130,1)",
                                    data: industryData
                                }
                            ]
                        };
                        var myChart = new Chart(ctx).Bar(data, {
                            barShowStroke: false,
                            multiTooltipTemplate: "<%= datasetLabel %> - <%= value %> Seconds",
                        });
                        var legendHolder = document.createElement('div');
                        legendHolder.innerHTML = myChart.generateLegend();

                        document.getElementById('legendDiv').appendChild(legendHolder.firstChild);
                    });

                });
                </script>


                <div id="resultTable">
                  <table width="600">
                    <caption>Server Performance Benchmarks</caption>
                    <thead>
                      <tr>
                        <th width="300">Test</th>
                        <th>Execution Time (seconds)</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <td><span class="simptip-position-right simptip-smooth" data-tooltip="Times 10,000 mathematical functions in PHP">Math</span></td>
                        <td><?php echo $arr_benchmark['benchmark']['math']; ?></td>
                      </tr>
                      <tr>
                        <td><span class="simptip-position-right simptip-smooth" data-tooltip="Times 10,000 string manipulation functions in PHP">String Manipulation</span></td>
                        <td><?php echo $arr_benchmark['benchmark']['string']; ?></td>
                      </tr>
                      <tr>
                        <td><span class="simptip-position-right simptip-smooth" data-tooltip="Times 10,000 increments in PHP while and for loops">Loops</span></td>
                        <td><?php echo $arr_benchmark['benchmark']['loops']; ?></td>
                      </tr>
                      <tr>
                        <td><span class="simptip-position-right simptip-smooth" data-tooltip="Times 10,000 conditional checks in PHP">Conditionals</span></td>
                        <td><?php echo $arr_benchmark['benchmark']['ifelse']; ?></td>
                      </tr>
                      <tr>
                        <td><span class="simptip-position-right simptip-smooth" data-tooltip="Time it takes to establish a Mysql Connection">Mysql Connect</span></td>
                        <td><?php echo $arr_benchmark['benchmark']['mysql_connect']; ?></td>
                      </tr>
                      <tr>
                        <td><span class="simptip-position-right simptip-smooth" data-tooltip="Time it takes to select Mysql database">Mysql Select Database</span></td>
                        <td><?php echo $arr_benchmark['benchmark']['mysql_select_db']; ?></td>
                      </tr>
                      <tr>
                        <td><span class="simptip-position-right simptip-smooth" data-tooltip="Time it takes to query Mysql version information">Mysql Query Version</span></td>
                        <td><?php echo $arr_benchmark['benchmark']['mysql_query_version']; ?></td>
                      </tr>
                      <tr>
                        <td><span class="simptip-position-right simptip-smooth" data-tooltip="Time it takes for 1,000,000 ENCODE()s with a random seed">Mysql Query Benchmark</span></td>
                        <td><?php echo $arr_benchmark['benchmark']['mysql_query_benchmark']; ?></td>
                      </tr>
                    </tbody>
                    <tfoot>
                      <tr>
                        <th>Total Time (seconds)</th>
                        <th><?php echo $arr_benchmark['total']; ?></th>
                      </tr>
                    </tfoot>
                  </table>
                  <br />
                  <table width="600">
                    <caption><span class="simptip-position-bottom simptip-multiline simptip-smooth" data-tooltip="Performs 250 Insert, Select, Update and Delete functions through $wpdb">WordPress Performance Benchmark</span></caption>
                    <thead>
                      <tr>
                        <th width="300">Execution Time (seconds)</th>
                        <th>Queries Per Second</th>
                      </tr>
                    </thead>
                    <tfoot>
                      <tr>
                        <td><?php echo $arr_wordpress['time']; ?></td>
                        <td><?php echo $arr_wordpress['queries']; ?></td>
                      </tr>
                    </tfoot>
                  </table>
                  <br />
                  <table width="600">
                    <caption>Your Server Information</caption>
                    <thead>
                      <tr>
                        <th width="300">Test</th>
                        <th>Result</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <td>WPPerformanceTester Version</td>
                        <td><?php echo $arr_benchmark['version']; ?></td>
                      </tr>
                      <tr>
                        <td>System Time</td>
                        <td><?php echo $arr_benchmark['sysinfo']['time']; ?></td>
                      </tr>
                      <tr>
                        <td>Platform</td>
                        <td><?php echo $arr_benchmark['sysinfo']['platform']; ?></td>
                      </tr>
                      <tr>
                        <td>Server Name</td>
                        <td><?php echo $arr_benchmark['sysinfo']['server_name'] ; ?></td>
                      </tr>
                      <tr>
                        <td>Server Address</td>
                        <td><?php echo $arr_benchmark['sysinfo']['server_addr'] ; ?></td>
                      </tr>
                      <tr>
                        <td>MySql Server</td>
                        <td><?php echo DB_HOST; ?></td>
                      </tr>
                    </tbody>
                  </table>                  
                </div>                                
            <br />
            <br />
            <form target="_blank" method="post" action="https://wphreviews.com/wpperformancetester/" class="basic-grey">
                <h2>Share Your Results &amp; Write a Review</h2>
                <p>All submitted data may be published. Do not include any personal information you do not want publicly listed. 
                    Your data helps us maintain industry performance averages and provide users with real web hosting reviews.</p>
                <input type="submit" value="Share Results Only (Do not fill out form)">
                <input type="hidden" name="benchresult" value="<?php echo urlencode(json_encode($arr_benchmark)); ?>">
                <input type="hidden" name="wordpressresult" value="<?php echo urlencode(json_encode($arr_wordpress)); ?>">
                <h3>What Web Host do you use?</h3>
                <input type="text" name="host" length="50" placeholder="Company Name">
                <h3>Please tell us about your experience</h3>
                <br />The Good
                <br /><textarea name="reviewpros" rows="4" cols="50" placeholder="Please list the pros"></textarea>
                <br />The Bad
                <br /><textarea name="reviewcons" rows="4" cols="50" placeholder="Please list the cons"></textarea>
                <br />Anything Else?
                <br /><textarea name="reviewother" rows="4" cols="50" placeholder="Anything else you would like to add?"></textarea>
                <h3>How would you rate your web host overall?</h3>
                <input type="radio" name="rating" value="1"><label>1 - The Worst</label>
                <input type="radio" name="rating" value="2"><label>2</label>
                <input type="radio" name="rating" value="3"><label>3</label>
                <input type="radio" name="rating" value="4"><label>4</label>
                <input type="radio" name="rating" value="5"><label>5 - The Best</label>
                <br /><br />
                <h3>Your Information</h3>
                Name &nbsp;&nbsp;&nbsp;
                <input type="text" name="name" length="50" placeholder="Your Name">
                <br />Website
                <input type="text" name="website" length="50" placeholder="Your Website">
                <br />Twitter &nbsp;
                <input type="text" name="twitter" length="50" placeholder="@username">
                <br /><br />
                <input type="submit" value="Submit Review + Results">
            </form>
            <?php
            }
            ?>



        </div>

        <?php
    }

    /**
     * See: http://plugin.michael-simpson.com/?page_id=31
     * @return array of option meta data.
     */
    public function getOptionMetaData() {
        //  http://plugin.michael-simpson.com/?page_id=31
        return array(
            //'_version' => array('Installed Version'), // Leave this one commented-out. Uncomment to test upgrades.
            'ATextInput' => array(__('Enter in some text', 'my-awesome-plugin')),
            'AmAwesome' => array(__('I like this awesome plugin', 'my-awesome-plugin'), 'false', 'true'),
            'CanDoSomething' => array(__('Which user role can do something', 'my-awesome-plugin'),
                                        'Administrator', 'Editor', 'Author', 'Contributor', 'Subscriber', 'Anyone')
        );
    }

//    protected function getOptionValueI18nString($optionValue) {
//        $i18nValue = parent::getOptionValueI18nString($optionValue);
//        return $i18nValue;
//    }

    protected function initOptions() {
        $options = $this->getOptionMetaData();
        if (!empty($options)) {
            foreach ($options as $key => $arr) {
                if (is_array($arr) && count($arr) > 1) {
                    $this->addOption($key, $arr[1]);
                }
            }
        }
    }

    public function getPluginDisplayName() {
        return 'WP Performance Tester';
    }

    protected function getMainPluginFileName() {
        return 'wp-performance-tester.php';
    }

    /**
     * See: http://plugin.michael-simpson.com/?page_id=101
     * Called by install() to create any database tables if needed.
     * Best Practice:
     * (1) Prefix all table names with $wpdb->prefix
     * (2) make table names lower case only
     * @return void
     */
    protected function installDatabaseTables() {
        //        global $wpdb;
        //        $tableName = $this->prefixTableName('mytable');
        //        $wpdb->query("CREATE TABLE IF NOT EXISTS `$tableName` (
        //            `id` INTEGER NOT NULL");
    }

    /**
     * See: http://plugin.michael-simpson.com/?page_id=101
     * Drop plugin-created tables on uninstall.
     * @return void
     */
    protected function unInstallDatabaseTables() {
        //        global $wpdb;
        //        $tableName = $this->prefixTableName('mytable');
        //        $wpdb->query("DROP TABLE IF EXISTS `$tableName`");
    }


    /**
     * Perform actions when upgrading from version X to version Y
     * See: http://plugin.michael-simpson.com/?page_id=35
     * @return void
     */
    public function upgrade() {
    }

    public function enqueue_scripts_and_style( $hook ) {
        if ( $hook != 'tools_page_WPPerformanceTester_PluginSettings' ) {
            return;
        }
        wp_enqueue_script( 'chart-js', plugins_url('/js/Chart.js', __FILE__) );
        wp_enqueue_script( 'jquery');
        wp_enqueue_style( 'wppt-style', plugins_url('/css/wppt.css', __FILE__) );
        wp_enqueue_style( 'simptip-style', plugins_url('/css/simptip.css', __FILE__) );
    }

    public function addActionsAndFilters() {

        // Add options administration page
        // http://plugin.michael-simpson.com/?page_id=47
        add_action('admin_menu', array( $this, 'addSettingsSubMenuPage'));
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts_and_style' ) );
    }
}
