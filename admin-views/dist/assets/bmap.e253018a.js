import{B as b,c as B,b as T,m as E,a as S,e as z,f as x,r as L,g as N}from"./index.0a7f18a5.js";function r(t,n){this._bmap=t,this.dimensions=["lng","lat"],this._mapOffset=[0,0],this._api=n,this._projection=new BMap.MercatorProjection}r.prototype.type="bmap";r.prototype.dimensions=["lng","lat"];r.prototype.setZoom=function(t){this._zoom=t};r.prototype.setCenter=function(t){this._center=this._projection.lngLatToPoint(new BMap.Point(t[0],t[1]))};r.prototype.setMapOffset=function(t){this._mapOffset=t};r.prototype.getBMap=function(){return this._bmap};r.prototype.dataToPoint=function(t){var n=new BMap.Point(t[0],t[1]),e=this._bmap.pointToOverlayPixel(n),a=this._mapOffset;return[e.x-a[0],e.y-a[1]]};r.prototype.pointToData=function(t){var n=this._mapOffset;return t=this._bmap.overlayPixelToPoint({x:t[0]+n[0],y:t[1]+n[1]}),[t.lng,t.lat]};r.prototype.getViewRect=function(){var t=this._api;return new b(0,0,t.getWidth(),t.getHeight())};r.prototype.getRoamTransform=function(){return B()};r.prototype.prepareCustoms=function(){var t=this.getViewRect();return{coordSys:{type:"bmap",x:t.x,y:t.y,width:t.width,height:t.height},api:{coord:T(this.dataToPoint,this),size:T(V,this)}}};r.prototype.convertToPixel=function(t,n,e){return this.dataToPoint(e)};r.prototype.convertFromPixel=function(t,n,e){return this.pointToData(e)};function V(t,n){return n=n||[0,0],E([0,1],function(e){var a=n[e],o=t[e]/2,f=[],l=[];return f[e]=a-o,l[e]=a+o,f[1-e]=l[1-e]=n[1-e],Math.abs(this.dataToPoint(f)[e]-this.dataToPoint(l)[e])},this)}var _;r.dimensions=r.prototype.dimensions;function H(){function t(n){this._root=n}return t.prototype=new BMap.Overlay,t.prototype.initialize=function(n){return n.getPanes().labelPane.appendChild(this._root),this._root},t.prototype.draw=function(){},t}r.create=function(t,n){var e,a=n.getDom();return t.eachComponent("bmap",function(o){var f=n.getZr().painter,l=f.getViewportRoot();if(typeof BMap=="undefined")throw new Error("BMap api is not loaded");if(_=_||H(),e)throw new Error("Only one bmap component can exist");var i;if(!o.__bmap){var p=a.querySelector(".ec-extension-bmap");p&&(l.style.left="0px",l.style.top="0px",a.removeChild(p)),p=document.createElement("div"),p.className="ec-extension-bmap",p.style.cssText="position:absolute;width:100%;height:100%",a.appendChild(p);var s=o.get("mapOptions");s&&(s=S(s),delete s.mapType),i=o.__bmap=new BMap.Map(p,s);var d=new _(l);i.addOverlay(d),f.getViewportRootOffset=function(){return{offsetLeft:0,offsetTop:0}}}i=o.__bmap;var m=o.get("center"),c=o.get("zoom");if(m&&c){var y=i.getCenter(),v=i.getZoom(),u=o.centerOrZoomChanged([y.lng,y.lat],v);if(u){var w=new BMap.Point(m[0],m[1]);i.centerAndZoom(w,c)}}e=new r(i,n),e.setMapOffset(o.__mapOffset||[0,0]),e.setZoom(c),e.setCenter(m),o.coordinateSystem=e}),t.eachSeries(function(o){o.get("coordinateSystem")==="bmap"&&(o.coordinateSystem=e)}),e&&[e]};function R(t,n){return t&&n&&t[0]===n[0]&&t[1]===n[1]}z({type:"bmap",getBMap:function(){return this.__bmap},setCenterAndZoom:function(t,n){this.option.center=t,this.option.zoom=n},centerOrZoomChanged:function(t,n){var e=this.option;return!(R(t,e.center)&&n===e.zoom)},defaultOption:{center:[104.114129,37.550339],zoom:5,mapStyle:{},mapStyleV2:{},mapOptions:{},roam:!1}});function Z(t){for(var n in t)if(t.hasOwnProperty(n))return!1;return!0}x({type:"bmap",render:function(t,n,e){var a=!0,o=t.getBMap(),f=e.getZr().painter.getViewportRoot(),l=t.coordinateSystem,i=function(w,D){if(!a){var O=f.parentNode.parentNode.parentNode,h=[-parseInt(O.style.left,10)||0,-parseInt(O.style.top,10)||0],g=f.style,C=h[0]+"px",P=h[1]+"px";g.left!==C&&(g.left=C),g.top!==P&&(g.top=P),l.setMapOffset(h),t.__mapOffset=h,e.dispatchAction({type:"bmapRoam",animation:{duration:0}})}};function p(){a||e.dispatchAction({type:"bmapRoam",animation:{duration:0}})}o.removeEventListener("moving",this._oldMoveHandler),o.removeEventListener("moveend",this._oldMoveHandler),o.removeEventListener("zoomend",this._oldZoomEndHandler),o.addEventListener("moving",i),o.addEventListener("moveend",i),o.addEventListener("zoomend",p),this._oldMoveHandler=i,this._oldZoomEndHandler=p;var s=t.get("roam");s&&s!=="scale"?o.enableDragging():o.disableDragging(),s&&s!=="move"?(o.enableScrollWheelZoom(),o.enableDoubleClickZoom(),o.enablePinchToZoom()):(o.disableScrollWheelZoom(),o.disableDoubleClickZoom(),o.disablePinchToZoom());var d=t.__mapStyle,m=t.get("mapStyle")||{},c=JSON.stringify(m);JSON.stringify(d)!==c&&(Z(m)||o.setMapStyle(S(m)),t.__mapStyle=JSON.parse(c));var y=t.__mapStyle2,v=t.get("mapStyleV2")||{},u=JSON.stringify(v);JSON.stringify(y)!==u&&(Z(v)||o.setMapStyleV2(S(v)),t.__mapStyle2=JSON.parse(u)),a=!1}});L("bmap",r);N({type:"bmapRoam",event:"bmapRoam",update:"updateLayout"},function(t,n){n.eachComponent("bmap",function(e){var a=e.getBMap(),o=a.getCenter();e.setCenterAndZoom([o.lng,o.lat],a.getZoom())})});var J="1.0.0";export{J as version};
