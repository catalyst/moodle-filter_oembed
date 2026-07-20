// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Client-side rendering for oembed content.
 *
 * @module     filter_oembed/clientrender
 * @copyright  2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery', 'core/log'], function($, Log) {

    /**
     * Initialize client-side rendering for oembed content.
     */
    var init = function() {
        $('.oembed-client-render').each(function() {
            var $container = $(this);
            
            if ($container.data('processed')) {
                return;
            }
            $container.data('processed', true);
            
            var oembedUrl = $container.data('oembed-url');
            var originalUrl = $container.data('original-url');
            var params = $container.data('params');
            var withCredentials = $container.data('with-credentials') === 1;

            if (!oembedUrl) {
                Log.debug('filter_oembed/clientrender: No oembed URL provided');
                $container.html('<a href="' + originalUrl + '">' + originalUrl + '</a>');
                return;
            }

            $.ajax({
                url: oembedUrl,
                dataType: 'json',
                xhrFields: {
                    withCredentials: withCredentials
                },
                success: function(data) {
                    if (data && data.html) {
                        var embed = data.html;
                        
                        if (params) {
                            var paramStr = '';
                            for (var key in params) {
                                if (params.hasOwnProperty(key)) {
                                    paramStr += '&' + key + '=' + encodeURIComponent(params[key]);
                                }
                            }
                            embed = embed.replace('?feature=oembed', '?feature=oembed' + paramStr);
                        }
                        
                        var aspectRatio = 0;
                        if (data.width && data.height) {
                            aspectRatio = data.height / data.width;
                        }
                        
                        if (aspectRatio > 0) {
                            var padding = aspectRatio * 100;
                            var paddiv = '<div class="oembed-responsive-pad" style="padding-top:' + padding + '%"></div>';
                            $container.html('<div class="oembed-content oembed-responsive">' + embed + paddiv + '</div>');
                        } else {
                            $container.html('<div class="oembed-content">' + embed + '</div>');
                        }
                    } else {
                        Log.debug('filter_oembed/clientrender: No HTML in oembed response for ' + originalUrl);
                        $container.html('<a href="' + originalUrl + '">' + originalUrl + '</a>');
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    Log.debug('filter_oembed/clientrender: Error loading oembed content for ' + originalUrl + 
                              ' - ' + textStatus + ': ' + errorThrown);
                    $container.html('<a href="' + originalUrl + '">' + originalUrl + '</a>');
                }
            });
        });
    };

    return {
        init: init
    };
});
