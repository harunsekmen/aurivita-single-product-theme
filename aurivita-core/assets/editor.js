(() => {
  const {createElement:h,Fragment}=wp.element, {InspectorControls,MediaUpload,MediaUploadCheck,useBlockProps}=wp.blockEditor;
  const {PanelBody,TextControl,TextareaControl,ToggleControl,SelectControl,RangeControl,Button}=wp.components;
  const ServerSideRender=wp.serverSideRender.default || wp.serverSideRender;
  const schemas=window.AurivitaEditor?.schemas || {};
  function defaults(type,schema){const preset=schema.presets?.[0] || {};const settings={};(schema.settings || []).forEach(f=>{if(f.id && f.default!==undefined)settings[f.id]=f.default;}); const blocks={},order=[];(preset.blocks || []).forEach((block,i)=>{const id='block_'+i; blocks[id]={type:block.type,settings:block.settings || {}};order.push(id);});return {type,settings,blocks,block_order:order};}
  function control(field,value,set,key){
    if(!field.id)return null;const label=field.label || field.id;
    if(['product','page','blog'].includes(field.type))return h(SelectControl,{key,label,value:String(value || ''),options:[{label:field.type==='product'?'Ana ürünü kullan':'Seçin',value:''},...(window.AurivitaEditor?.resources?.[field.type] || [])],onChange:v=>set(/^\d+$/.test(v)?Number(v):v)});
    if(field.type==='checkbox')return h(ToggleControl,{key,label,checked:!!value,onChange:set});
    if(field.type==='range')return h(RangeControl,{key,label,value:Number(value ?? field.default ?? field.min),min:field.min,max:field.max,step:field.step,onChange:set});
    if(field.type==='select' || field.type==='radio')return h(SelectControl,{key,label,value:value ?? field.default,options:field.options || [],onChange:set});
    if(field.type==='image_picker' || field.type==='video')return h('div',{key,style:{marginBottom:'18px'}},h(TextControl,{label:label+' (URL veya medya ID)',value:String(value || ''),onChange:v=>set(/^\d+$/.test(v)?Number(v):v)}),h(MediaUploadCheck,{},h(MediaUpload,{allowedTypes:[field.type==='video'?'video':'image'],value:Number(value)||undefined,onSelect:media=>set(media.id),render:({open})=>h(Button,{variant:'secondary',onClick:open},'Medya seç')})));
    const multiline=['textarea','richtext','inline_richtext','html'].includes(field.type);
    return h(multiline?TextareaControl:TextControl,{key,label,value:String(value ?? field.default ?? ''),help:field.type==='richtext'?'Paragraf ve bağlantılar için HTML kullanılabilir.':field.info,onChange:set});
  }
  Object.entries(schemas).forEach(([type,schema])=>{
    if(['av-header','av-footer'].includes(type))return;
    wp.blocks.registerBlockType('aurivita/'+type,{apiVersion:3,title:schema.title || type,icon:'layout',category:'aurivita',attributes:{section:{type:'object',default:defaults(type,schema)},sectionId:{type:'string',default:''}},supports:{html:false},edit:({attributes,setAttributes,clientId})=>{
      const section=attributes.section || defaults(type,schema), update=(change)=>setAttributes({section:{...section,...change},sectionId:attributes.sectionId || 'av-'+clientId});
      const settingControl=(field,blockId)=>control(field,blockId?section.blocks?.[blockId]?.settings?.[field.id]:section.settings?.[field.id],value=>{
        if(blockId)update({blocks:{...section.blocks,[blockId]:{...section.blocks[blockId],settings:{...section.blocks[blockId].settings,[field.id]:value}}}});
        else update({settings:{...section.settings,[field.id]:value}});
      },(blockId || 'section')+'-'+field.id);
      const order=section.block_order || Object.keys(section.blocks || {});
      const panels=[h(PanelBody,{title:'Bölüm ayarları',initialOpen:true,key:'settings'},(schema.settings || []).map(f=>settingControl(f)))];
      order.forEach((id,index)=>{const block=section.blocks[id],definition=schema.blocks?.find(b=>b.type===block.type);if(!block||!definition)return;
        panels.push(h(PanelBody,{title:(definition.name?.startsWith('t:')?'İçerik grubu '+(index+1):definition.name) || block.type,initialOpen:false,key:id},(definition.settings || []).map(f=>settingControl(f,id)),h('div',{style:{display:'flex',gap:'8px'}},h(Button,{variant:'secondary',disabled:index===0,onClick:()=>{const next=[...order];[next[index-1],next[index]]=[next[index],next[index-1]];update({block_order:next});}},'Yukarı'),h(Button,{variant:'secondary',disabled:index===order.length-1,onClick:()=>{const next=[...order];[next[index+1],next[index]]=[next[index],next[index+1]];update({block_order:next});}},'Aşağı'),h(Button,{isDestructive:true,onClick:()=>{const blocks={...section.blocks};delete blocks[id];update({blocks,block_order:order.filter(v=>v!==id)});}},'Kaldır'))));
      });
      panels.push(h(PanelBody,{title:'İçerik ekle',initialOpen:false,key:'add'},(schema.blocks || []).map(def=>h(Button,{key:def.type,variant:'secondary',disabled:(def.limit && order.filter(id=>section.blocks[id]?.type===def.type).length>=def.limit) || (schema.max_blocks && order.length>=schema.max_blocks),onClick:()=>{const id='b_'+Date.now();const settings={};(def.settings||[]).forEach(f=>{if(f.id)settings[f.id]=f.default ?? '';});update({blocks:{...section.blocks,[id]:{type:def.type,settings}},block_order:[...order,id]});}},def.name?.startsWith('t:')?def.type:def.name))));
      return h(Fragment,{},h(InspectorControls,{},panels),h('div',useBlockProps(),h('div',{style:{padding:'12px',background:'#FAF2F2',borderLeft:'3px solid #B5191D'}},schema.title || type),h(ServerSideRender,{block:'aurivita/'+type,attributes,httpMethod:'POST'})));
    },save:()=>null});
  });
})();
