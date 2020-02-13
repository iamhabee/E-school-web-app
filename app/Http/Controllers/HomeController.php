<?php

namespace App\Http\Controllers;
use Auth;
use App\Traits\Utilities;
use Illuminate\Http\Request;
use App\School;
use App\Teacher;
use App\Parents;
use App\Student;
use App\User;

class HomeController extends Controller
{
  use Utilities;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
      $cat_code = Auth::user()->user_category; // get current user category
      if ($cat_code == $this->userRole('SUPERADMIN')) {

        $school = School::all()->count();
        $student = Student::all()->count();
        $teacher = Teacher::all()->count();
        $parents = Parents::all()->count();
        $total = User::all()->count();
        $data = array(
          'school' => $school,
          'teacher' => $teacher,
          'total' => $total,
          'parents' => $parents,
          'student' => $student
        );
        // $datas = $data['school'];
        // dd($datas);
          return view('dashboard', ['data'=>$data]);
      }elseif ($cat_code == $this->userRole('ADMIN')) {

        $id = Auth::user()->school_id;
        $school_name = School::where('id', $id)->pluck('school_name')->first();
        $school = User::where('school_id', $id)->get()->count();
        $student = Student::where('school_id', $id)->get()->count();
        // dd($student);
        $teacher = Teacher::where('school_id', $id)->get()->count();
        $parents = Parents::where('school_id', $id)->get()->count();
        // $total = User::all()->count();
        // $student = School::find(Auth::id());

        $data = array(
          'school_name' => $school_name,
          'school' => $school,
          'teacher' => $teacher,
          'parents' => $parents,
          'student' => $student
        );
        return view('dashboard', ['data'=>$data]);

      }elseif ($cat_code == $this->userRole('PARENT')) {
        $id = Auth::user()->school_id;

        $school_name = School::where('id', $id)->pluck('school_name')->first();
        $children = Student::where('secondary_contact_id', Auth::user()->external_table_id || 'primary_contact_id', Auth::user()->external_table_id)->get();

        $data = array(
          'school_name' => $school_name,
          'children' => $children
        );
        // dd($data['children']);
        dd($this->array_add_multiple($children));

          // return view('parent', ['data'=>$data]);
      }else {
        $id = Auth::user()->school_id;
        $uid = Auth::user()->external_table_id;

        $school_name = School::where('id', $id)->pluck('school_name')->first();
        $teacher_data = Teacher::where('id', $uid)->first();
        $children = Student::where('school_id', $id)->where('class_name', $teacher_data->class_assigned)->get()->toArray();

        $data = array(
          'school_name' => $school_name,
          'children' => $children
        );
         // dd($data['school_name']);
         dd($this->array_add_multiple($children));
          return view('parent', ['data' => $data]);
      }
    }

    public function studentView($id)
    {
      $sch_id = Auth::user()->school_id;
      $children = Student::where('id', $id)->first();
      $school= $children->school;

      $data = array(
        'school_name' => $school->school_name,
        'children' => $children
      );
        return view('studentView',  ['data' => $data]);
    }

    function array_add_multiple($children)
    {
      foreach($children as $child )
      {
        $school_name = School::where('id', $child->id)->pluck('school_name')->first();
        $array[$child->first_name] = $school_name;
          // $array = array_add($items, $child->first_name, $school_name);
      }
    return $array;
}
}
