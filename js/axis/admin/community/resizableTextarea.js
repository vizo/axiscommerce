/**
 * Axis
 * 
 * This file is part of Axis.
 * 
 * Axis is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * Axis is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with Axis.  If not, see <http://www.gnu.org/licenses/>.
 */

Ext.onReady(function(){
    
    Ext.QuickTips.init();
    
    var textArea = new Ext.form.TextArea({
        listeners: {
            render: {
                fn: function(f){
                    f.resizer = new Ext.Resizable(f.getEl(),{handles:'s,se,e',wrap:true});
                    f.resizer.on('resize',function(){delete f.anchor;});
                }
            }
        },
        onResize: function(){
            Ext.form.TextArea.superclass.onResize.apply(this, arguments);
            var r = this.resizer;
            var csize = r.getResizeChild().getSize();
            r.el.setSize(csize.width, csize.height);
        },
        anchor: '95% 100%',
        name: 'resizable_area',
        allowBlank: true,
        id: 'resizable_area'
    })
    
})