<?php
/**
* 后台权限
*/

namespace app\backend\controller;

use think\Controller;
use think\Request;
use app\backend\model\AuthRule as MasterModel;

class AdminRule extends Backend
{   

    public function index(){
        
        $postArr = $this->request->param();

        $pid = intval($postArr['pid']);

        $masterModel = new MasterModel();
        
        if(!empty($postArr)){
            foreach ($postArr as $k=>$vo) {
                if($vo!=='' && in_array($k, ['title'])){
                    $masterModel->where($k,'like',"%".$vo.'%');
                }
                if($vo!=='' && in_array($k, ['status'])){
                    $masterModel->where($k,'=',$vo);
                }
            }
            if($pid){
                $masterModel->where('pid','=',$pid);
            }
            if(!isset($postArr['status'])){
                $masterModel->where('status','>=',0);
            }
        }else{
            $masterModel->where('pid','=',$pid);
            $masterModel->where('status','>=',0);
        }

        $list = $masterModel->order('sort desc,id asc')->paginate(15,false,['query'=>$postArr]);

        // 获取分页显示
        $page = $list->render();

        foreach ($list as $key => $value) {
           $list[$key]->append(['is_menu_text']);
           $list[$key]->append(['status_text']);
        }
        
        // 模板变量赋值
        $this->assign('list', $list->toArray());
        $this->assign('page', $page);
        $this->assign('pid', $postArr['pid']);

        return $this->fetch();
    }
  
    //添加
    public function add(){
        if($this->request->isPost()){
            return $this->operate();
        }else{
            $postArr = $this->request->param();
            $info['pid'] = $postArr['pid'];
            $info['sort'] = 99;
            $this->assign('info',$info);
            return $this->fetch('operate');
        }
        
    }

    //修改
    public function update(){
        if($this->request->isPost()){
            return $this->operate();
        }else{
            $id = $this->request->param('id');
            if(empty($id)){
                $this->error('ID 为空');
            }

            $info = MasterModel::get($id)->toArray();
            $this->assign('info',$info);

            return $this->fetch('operate');
        }
    }

    private function operate(){
    
        if($this->request->isPost()){
            
            $postArr = $this->request->post();

            $id = $postArr['id'];

            $masterModel = new MasterModel;

            if(empty($id)){
                
               
                $do = $masterModel->addInfo($postArr);
                
                if(!is_numeric($do)){
                    return json(['status'=>0,'msg'=>$do]);
                }

                return json(['status'=>1,'msg'=>"添加成功",'url'=>url('index')]);
            }else{

                $do = $masterModel->updateInfo(['id'=>$id],$postArr);
                
                if(!is_numeric($do)){
                    return json(['status'=>0,'msg'=>$do]);
                }

                return json(['status'=>1,'msg'=>"修改成功",'url'=>url('index')]);
            }

        }
    
    }

    //删除
    public function del(){

        if(!$this->request->isAjax()){
            return json(['status'=>0,'msg'=>"非法"]);
        }

        $ids = $this->request->post('ids');
        if(empty($ids)){
            return json(['status'=>0,'msg'=>"参数有误"]);
        }

        $do = MasterModel::_update_status(['id'=>['in',$ids]],-1);
        if($do){
            return json(['status'=>1,'msg'=>"删除成功"]);
        }
            
        return json(['status'=>0,'msg'=>"删除失败"]);
    
    }


}