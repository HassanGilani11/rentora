import{u as F,y as N,D as z}from"./index.jlplx4ex.js";import{C as B}from"./Card.m3lmtg1o.js";import{G as j,a as G}from"./Row.o0q8mn7y.js";import{S as O}from"./Checkmark.d5kkjaf5.js";import{c as J}from"./index.npoectbv.js";import{S as M}from"./Download.cmimu09k.js";import{T as Q,a as W}from"./Row.ovxv3gcd.js";import"./translations.b896ab1m.js";import{_ as X}from"./_plugin-vue_export-helper.oebm7xum.js";import{_ as s}from"./default-i18n.hohxoesu.js";import{c as l,C as o,l as n,v as r,X as q,o as a,a as c,x as p,t as m,Q as H,k as E,F as _,b as h,G as b,J as D}from"./runtime-dom.esm-bundler.h3clfjuw.js";import"./helpers.cti0cl6i.js";import"./Tooltip.jx4casvt.js";import"./Caret.hnvbzqgq.js";import"./Slide.dop8j51m.js";const t="all-in-one-seo-pack",K={setup(){return{rootStore:F(),toolsStore:N()}},components:{CoreCard:B,GridColumn:j,GridRow:G,SvgCheckmark:O,SvgCopy:J,SvgDownload:M,TableColumn:Q,TableRow:W},data(){return{copied:!1,emailError:null,emailAddress:null,sendingEmail:!1,strings:{systemStatusInfo:s("System Status Info",t),downloadSystemInfoFile:s("Download System Info File",t),copyToClipboard:s("Copy to Clipboard",t),emailDebugInformation:s("Email Debug Information",t),submit:s("Submit",t),wordPress:s("WordPress",t),serverInfo:s("Server Info",t),activeTheme:s("Active Theme",t),muPlugins:s("Must-Use Plugins",t),activePlugins:s("Active Plugins",t),inactivePlugins:s("Inactive Plugins",t),copied:s("Copied!",t)}}},computed:{copySystemStatusInfo(){return JSON.stringify(this.rootStore.aioseo.data.status)}},methods:{onCopy(){this.copied=!0,setTimeout(()=>{this.copied=!1},2e3)},onError(){},downloadSystemStatusInfo(){const S=new Blob([JSON.stringify(this.rootStore.aioseo.data.status)],{type:"application/json"}),i=document.createElement("a");i.href=URL.createObjectURL(S),i.download=`aioseo-system-status-${z.now().toFormat("yyyy-MM-dd")}.json`,i.click(),URL.revokeObjectURL(i.href)},processEmailDebugInfo(){if(this.emailError=!1,!this.emailAddress||!/^\w+([.-]?\w+)*@\w+([.-]?\w+)*(\.\w{2,3})+$/.test(this.emailAddress)){this.emailError=!0;return}this.sendingEmail=!0,this.toolsStore.emailDebugInfo(this.emailAddress).then(()=>{this.emailAddress=null,this.sendingEmail=!1})}}},Y={class:"aioseo-tools-system-status-info"},Z={class:"actions"},$={class:"aioseo-settings-row"},ee={class:"settings-name"},se={class:"name"},oe={class:"settings-content"},te={class:"system-status-table"},ne=["title"];function re(S,i,ae,A,e,u){const T=r("svg-download"),f=r("base-button"),x=r("svg-copy"),P=r("svg-checkmark"),v=r("grid-column"),R=r("base-input"),L=r("grid-row"),w=r("table-column"),U=r("table-row"),V=r("core-card"),g=q("clipboard");return a(),l("div",Y,[o(V,{slug:"systemStatusInfo","header-text":e.strings.systemStatusInfo},{default:n(()=>[c("div",Z,[o(L,null,{default:n(()=>[o(v,{sm:"6",class:"left"},{default:n(()=>[o(f,{type:"blue",size:"small",onClick:u.downloadSystemStatusInfo},{default:n(()=>[o(T),p(" "+m(e.strings.downloadSystemInfoFile),1)]),_:1},8,["onClick"]),H((a(),E(f,{type:"blue",size:"small"},{default:n(()=>[e.copied?h("",!0):(a(),l(_,{key:0},[o(x),p(" "+m(e.strings.copyToClipboard),1)],64)),e.copied?(a(),l(_,{key:1},[o(P),p(" "+m(e.strings.copied),1)],64)):h("",!0)]),_:1})),[[g,u.copySystemStatusInfo,"copy"],[g,u.onCopy,"success"],[g,u.onError,"error"]])]),_:1}),o(v,{sm:"6",class:"right"},{default:n(()=>[o(R,{size:"small",placeholder:e.strings.emailDebugInformation,modelValue:e.emailAddress,"onUpdate:modelValue":i[0]||(i[0]=d=>e.emailAddress=d),class:b({"aioseo-error":e.emailError})},null,8,["placeholder","modelValue","class"]),o(f,{type:"blue",size:"small",onClick:u.processEmailDebugInfo,loading:e.sendingEmail},{default:n(()=>[p(m(e.strings.submit),1)]),_:1},8,["onClick","loading"])]),_:1})]),_:1})]),c("div",$,[(a(!0),l(_,null,D(A.rootStore.aioseo.data.status,(d,k)=>{var C;return a(),l(_,{key:k},[(C=d.results)!=null&&C.length?(a(),l("div",{key:0,class:b(["settings-group",["settings-group--"+k]])},[c("div",ee,[c("div",se,m(d.label),1)]),c("div",oe,[c("div",te,[(a(!0),l(_,null,D(d.results,(y,I)=>(a(),E(U,{key:I,class:b({even:I%2===0})},{default:n(()=>[o(w,{class:"system-status-header"},{default:n(()=>[c("span",{title:y.header},m(y.header),9,ne)]),_:2},1024),o(w,null,{default:n(()=>[p(m(y.value),1)]),_:2},1024)]),_:2},1032,["class"]))),128))])])],2)):h("",!0)],64)}),128))])]),_:1},8,["header-text"])])}const we=X(K,[["render",re]]);export{we as default};