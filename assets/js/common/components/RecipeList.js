import React from "react";
import Recipe from "./Recipe";
import {Link} from "react-router-dom";

const RecipeList = props => (
    <>
        {props.recipes.map((recipe, idx) => (
            <div key={idx}>
                <Link to={"/recipe/" + recipe.id}>
                    <Recipe key={idx} recipe={recipe} id={idx}/>
                </Link>
            </div>
        ))}
    </>
);

export default RecipeList;
