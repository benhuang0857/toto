<?php

namespace App\Admin\Controllers;

use App\RawSurvey;
use App\Admin\Actions\Tenant\ImportTenant;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class RawSurveyController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'RawSurvey';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new RawSurvey());

        $grid->filter(function($filter){
            $filter->disableIdFilter();
            $filter->like('respondent_serial', 'respondent_serial');
            $filter->like('respondent_id', 'respondent_id');
            $filter->like('acceptance_code', 'acceptance_code');
        });

        $grid->tools(function (Grid\Tools $tools) {
            $tools->append(new ImportTenant());
        });

        $grid->column('respondent_serial', __('Respondent serial'));
        $grid->column('respondent_id', __('Respondent id'));
        $grid->column('datacollection_status', __('Datacollection status'));
        $grid->column('start_time', __('Start time'));
        $grid->column('end_time', __('End time'));
        $grid->column('interview_length', __('Interview length'));
        $grid->column('db_wave', __('Db wave'));
        $grid->column('db_interviewdate', __('Db interviewdate'));
        $grid->column('quota_year', __('Quota year'));
        $grid->column('quota_month', __('Quota month'));
        $grid->column('quota_wave', __('Quota wave'));
        $grid->column('s_info', __('S info'));
        $grid->column('s1', __('S1'));
        $grid->column('q1', __('Q1'));
        $grid->column('q2', __('Q2'));
        $grid->column('q3', __('Q3'));
        $grid->column('q4', __('Q4'));
        $grid->column('q5', __('Q5'));
        $grid->column('q6', __('Q6'));
        $grid->column('q7', __('Q7'));
        $grid->column('q8', __('Q8'));
        $grid->column('q8_9', __('Q8 9'));
        $grid->column('q9', __('Q9'));
        $grid->column('q10', __('Q10'));
        $grid->column('q11', __('Q11'));
        $grid->column('q12', __('Q12'));
        $grid->column('q13', __('Q13'));
        $grid->column('q14', __('Q14'));
        $grid->column('rq14', __('Rq14'));
        $grid->column('cklow_score', __('Cklow score'));
        $grid->column('opinion_category', __('Opinion category'));
        $grid->column('complaint1', __('Complaint1'));
        $grid->column('complaint2', __('Complaint2'));
        $grid->column('recommend', __('Recommend'));
        $grid->column('s_region', __('S region'));
        $grid->column('s_category', __('S category'));
        $grid->column('s_person2', __('S person2'));
        $grid->column('s_person', __('S s_person'));
        $grid->column('send_date', __('Send date'));
        $grid->column('acceptance_code', __('Acceptance code'));
        $grid->column('product_code', __('Product code'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(RawSurvey::findOrFail($id));

        $show->field('respondent_serial', __('Respondent serial'));
        $show->field('respondent_id', __('Respondent id'));
        $show->field('datacollection_status', __('Datacollection status'));
        $show->field('start_time', __('Start time'));
        $show->field('end_time', __('End time'));
        $show->field('interview_length', __('Interview length'));
        $show->field('db_wave', __('Db wave'));
        $show->field('db_interviewdate', __('Db interviewdate'));
        $show->field('quota_year', __('Quota year'));
        $show->field('quota_month', __('Quota month'));
        $show->field('quota_wave', __('Quota wave'));
        $show->field('s_info', __('S info'));
        $show->field('s1', __('S1'));
        $show->field('q1', __('Q1'));
        $show->field('q2', __('Q2'));
        $show->field('q3', __('Q3'));
        $show->field('q4', __('Q4'));
        $show->field('q5', __('Q5'));
        $show->field('q6', __('Q6'));
        $show->field('q7', __('Q7'));
        $show->field('q8', __('Q8'));
        $show->field('q8_9', __('Q8 9'));
        $show->field('q9', __('Q9'));
        $show->field('q10', __('Q10'));
        $show->field('q11', __('Q11'));
        $show->field('q12', __('Q12'));
        $show->field('q13', __('Q13'));
        $show->field('q14', __('Q14'));
        $show->field('rq14', __('Rq14'));
        $show->field('cklow_score', __('Cklow score'));
        $show->field('opinion_category', __('Opinion category'));
        $show->field('complaint1', __('Complaint1'));
        $show->field('complaint2', __('Complaint2'));
        $show->field('recommend', __('Recommend'));
        $show->field('s_region', __('S region'));
        $show->field('s_category', __('S category'));
        $show->field('s_person2', __('S person2'));
        $show->field('s_person', __('S person'));
        $show->field('send_date', __('Send date'));
        $show->field('acceptance_code', __('Acceptance code'));
        $show->field('product_code', __('Product code'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new RawSurvey());

        $form->text('respondent_serial', __('Respondent serial'));
        $form->text('respondent_id', __('Respondent id'));
        $form->text('datacollection_status', __('Datacollection status'));
        $form->datetime('start_time', __('Start time'));
        $form->datetime('end_time', __('End time'));
        $form->text('interview_length', __('Interview length'));
        $form->text('db_wave', __('Db wave'));
        $form->text('db_interviewdate', __('Db interviewdate'));
        $form->text('quota_year', __('Quota year'));
        $form->text('quota_month', __('Quota month'));
        $form->text('quota_wave', __('Quota wave'));
        $form->text('s_info', __('S info'));
        $form->text('s1', __('S1'));
        $form->text('q1', __('Q1'));
        $form->text('q2', __('Q2'));
        $form->text('q3', __('Q3'));
        $form->text('q4', __('Q4'));
        $form->text('q5', __('Q5'));
        $form->text('q6', __('Q6'));
        $form->text('q7', __('Q7'));
        $form->text('q8', __('Q8'));
        $form->text('q8_9', __('Q8 9'));
        $form->text('q9', __('Q9'));
        $form->text('q10', __('Q10'));
        $form->text('q11', __('Q11'));
        $form->text('q12', __('Q12'));
        $form->text('q13', __('Q13'));
        $form->text('q14', __('Q14'));
        $form->text('rq14', __('Rq14'));
        $form->text('cklow_score', __('Cklow score'));
        $form->text('opinion_category', __('Opinion category'));
        $form->text('complaint1', __('Complaint1'));
        $form->text('complaint2', __('Complaint2'));
        $form->text('recommend', __('Recommend'));
        $form->text('s_region', __('S region'));
        $form->text('s_category', __('S category'));
        $form->text('s_person2', __('S person2'));
        $form->text('s_dealer2', __('S dealer2'));
        $form->text('send_date', __('Send date'));
        $form->text('acceptance_code', __('Acceptance code'));
        $form->text('product_code', __('Product code'));

        return $form;
    }
}
