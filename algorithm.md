BEGIN APPLICATION

// ---------- INITIAL SETUP / ONBOARDING ----------
DISPLAY setup_form

SET available_programs = ["Computer Science"]   // only option for now
SET current_year = SYSTEM.YEAR
SET dropdown_font_color = BLACK

INPUT student_name
INPUT current_GPA
INPUT completed_courses   // select from predefined CS course list

IF form_is_complete THEN
    SAVE student_profile (name, GPA, completed_courses, year, program)
    REDIRECT to DASHBOARD
ELSE
    DISPLAY error "Please complete all required fields"
END IF


// ---------- DASHBOARD ----------
DISPLAY student_name
DISPLAY CGPA with visibility_toggle (eye_button: hide/show)

DISPLAY completed_courses_list

DISPLAY sidebar_options:
    - Dashboard
    - Register Courses
    - Course Catalog


// ---------- COURSE CATALOG ----------
IF user_clicks "Course Catalog" THEN
    DISPLAY all_computer_science_courses
    ENABLE search_by(course_name OR course_id)
END IF


// ---------- REGISTER COURSES ----------
IF user_clicks "Register Courses" THEN
    DISPLAY searchable_course_list
    USER selects desired_courses
END IF


// ---------- PREREQUISITE CHECK ----------
IF user_clicks "Check Prerequisites" THEN
    FOR each selected_course IN desired_courses DO
        FETCH prerequisites FROM admin_database

        IF completed_courses DOES NOT satisfy prerequisites THEN
            DISPLAY error:
              "You are not qualified for this course: <course_name>"
            REMOVE course FROM desired_courses
            REDIRECT to Register_Courses_Tab
        END IF
    END FOR
END IF


// ---------- TIME SELECTION ----------
IF all_prerequisites_passed THEN
    FOR each course IN desired_courses DO
        DISPLAY available_sections_and_times
        USER selects preferred_time
    END FOR
END IF


// ---------- CLASH DETECTION ----------
IF user_clicks "Check Clashes" THEN
    IF timetable_has_clashes THEN
        DISPLAY clash_warning
        SUGGEST alternative_sections
    ELSE
        ENABLE "Auto Register Courses" button
    END IF
END IF


// ---------- AUTO REGISTRATION ----------
IF user_clicks "Auto Register Courses" THEN
    REDIRECT to Microsoft_Login
END IF


// ---------- AUTHENTICATION ----------
IF Microsoft_Login SUCCESSFUL THEN
    AUTHENTICATE with MyCAMU
    SAVE registration_details
    SET registration_status = "Awaiting Auto Registration"
    REDIRECT to DASHBOARD
ELSE
    DISPLAY error "Login failed. Try again."
END IF


// ---------- LIVE STATUS FEEDBACK ----------
WHILE registration_status != "Completed" DO
    DISPLAY live_status_updates
END WHILE

DISPLAY "Registration Successful"

END APPLICATION
